<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * ProductsImport — importador de catálogo desde Excel.
 *
 * ══ ARQUITECTURA ══════════════════════════════════════════════════════════
 *
 * ANTES (incorrecto):
 *   1 fila Excel → 1 Product
 *   Esto genera duplicados masivos cuando el mismo producto tiene múltiples
 *   códigos OEM o variantes.
 *
 * AHORA (correcto):
 *   N filas con mismo (fig + code_gs) → 1 Product + N ProductItems
 *
 *   Product   = producto base identificado por figura + código GS
 *   ProductItem = cada fila del Excel; contiene OEM code, aplicación,
 *                 datos dinámicos de variantes (G60, G85, etc.)
 *
 * ══ COLUMNAS DEL EXCEL ════════════════════════════════════════════════════
 *
 * CONOCIDAS (mapeadas a campos de BD):
 *   marca              → Brand.name / Brand.slug
 *   modelo_caja        → Gearbox.name / Gearbox.slug
 *   modelos_vehiculo   → ProductItem.application + application_type
 *   fig                → Product.figure
 *   cod_gs             → Product.code_gs
 *   codigo_oem         → ProductItem.oem_code
 *   nombre             → Product.name (JSON ES)
 *   descripcion        → Product.description (JSON ES)
 *   referencia_original   → ProductItem.extra_data['referencia_original']
 *   referencias_adicionales → ProductItem.extra_data['referencias_adicionales']
 *   notas              → ProductItem.notes
 *
 * DINÁMICAS (detectadas automáticamente):
 *   Cualquier columna NO listada arriba → ProductItem.extra_data[columna]
 *   Valor "x", "si", "yes", "1", "true" → true
 *   Valor vacío, "no", "0"              → false
 *
 * ══ LÓGICA DE MODELOS_VEHICULO ════════════════════════════════════════════
 *
 *   Si modelos_vehiculo está vacío → application_type = 'generic'
 *     Significa: el producto aplica a TODA la caja (no a un modelo específico)
 *
 *   Si modelos_vehiculo tiene valor → application_type = 'specific'
 *     Significa: el producto aplica sólo a esos modelos de vehículo
 *
 * ══ QUÉ NO HACE EL IMPORTADOR ════════════════════════════════════════════
 *
 *   ✗ NO toca gearbox.extra_field_1 / extra_field_2
 *     (son campos manuales, se editan desde el panel admin)
 *
 *   ✗ NO rechaza filas con modelos_vehiculo vacío (es dato válido)
 *
 *   ✗ NO duplica productos (agrupa por fig+code_gs antes de crear)
 */
class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    /** Productos base creados en esta importación */
    public int $created = 0;

    /** Productos base actualizados en esta importación */
    public int $updated = 0;

    /** Items creados en esta importación */
    public int $itemsCreated = 0;

    /** Items actualizados en esta importación */
    public int $itemsUpdated = 0;

    /** Errores ocurridos (no fatales — el resto continúa) */
    public array $errors = [];

    /**
     * Columnas conocidas del Excel.
     * Todo lo que NO esté aquí se trata como columna dinámica
     * y se guarda en extra_data del ProductItem.
     */
    private const KNOWN_COLUMNS = [
        'marca',
        'modelo_caja',
        'modelos_vehiculo',
        'fig',
        'cod_gs',
        'codigo_oem',
        'nombre',
        'descripcion',
        'referencia_original',
        'referencias_adicionales',
        'notas',
    ];

    /*
    |--------------------------------------------------------------------------
    | ENTRY POINT
    |--------------------------------------------------------------------------
    */

    public function collection(Collection $rows): void
    {
        // 1. Normalizar todas las filas (claves a minúsculas, trim de valores)
        $normalized = $this->normalizeRows($rows);

        // 2. Agrupar por (brand, gearbox, fig, code_gs)
        //    → cada grupo = 1 producto base con N items
        $groups = $this->groupRowsByProduct($normalized);

        // 3. Procesar cada grupo
        foreach ($groups as $groupKey => $groupRows) {
            try {
                $this->processGroup($groupKey, $groupRows);
            } catch (\Throwable $e) {
                $this->errors[] = "[Grupo {$groupKey}] " . $e->getMessage();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZACIÓN
    |--------------------------------------------------------------------------
    */

    /**
     * Normaliza las claves de cada fila: minúsculas + trim.
     * También hace trim de valores string.
     */
    private function normalizeRows(Collection $rows): array
    {
        return $rows->map(function ($row) {
            $arr = $row->toArray();

            return array_combine(
                array_map(fn ($k) => strtolower(trim((string) $k)), array_keys($arr)),
                array_map(fn ($v) => is_string($v) ? trim($v) : $v, array_values($arr))
            );
        })->toArray();
    }

    /*
    |--------------------------------------------------------------------------
    | AGRUPACIÓN — NÚCLEO DE LA ARQUITECTURA
    |--------------------------------------------------------------------------
    */

    /**
     * Agrupa filas por clave de producto único:
     *   "{brand_slug}|{gearbox_slug}|{figure}|{code_gs}"
     *
     * Si fig y code_gs están ambos vacíos, se usa el oem_code como
     * diferenciador de último recurso (cada OEM = producto separado).
     *
     * @param  array[]  $rows
     * @return array<string, array[]>  groupKey → array de filas
     */
    private function groupRowsByProduct(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $brandSlug   = Str::slug(trim($row['marca']        ?? ''));
            $gearboxSlug = Str::slug(trim($row['modelo_caja']  ?? ''));
            $fig         = trim($row['fig']     ?? '');
            $codeGs      = trim($row['cod_gs']  ?? '');
            $oemCode     = trim($row['codigo_oem'] ?? '');

            // La clave identifica el producto base
            if ($fig !== '' || $codeGs !== '') {
                // Caso normal: producto definido por figura y/o code_gs
                $productKey = "{$brandSlug}|{$gearboxSlug}|{$fig}|{$codeGs}";
            } else {
                // Fallback: ni fig ni code_gs → el OEM code define el producto
                // (caso extremo que no debería darse, pero el sistema no rompe)
                $productKey = "{$brandSlug}|{$gearboxSlug}|__oem__{$oemCode}";
            }

            $groups[$productKey][] = $row;
        }

        return $groups;
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESAMIENTO DE GRUPO (1 Product + N ProductItems)
    |--------------------------------------------------------------------------
    */

    private function processGroup(string $groupKey, array $rows): void
    {
        $firstRow    = $rows[0];
        $brandName   = trim($firstRow['marca']       ?? '');
        $gearboxName = trim($firstRow['modelo_caja'] ?? '');

        if (! $brandName || ! $gearboxName) {
            $this->errors[] = "[Grupo {$groupKey}] Sin marca o modelo_caja — se omite.";
            return;
        }

        // ── 1. Brand (firstOrCreate) ────────────────────────────────────────
        $brand = Brand::firstOrCreate(
            ['slug' => Str::slug($brandName)],
            [
                'name'      => ['es' => $brandName, 'en' => $brandName, 'pt' => $brandName],
                'is_active' => true,
            ]
        );

        // ── 2. Gearbox (firstOrCreate) ──────────────────────────────────────
        $gearboxSlug = Str::slug($gearboxName);

        $gearbox = Gearbox::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => $gearboxSlug],
            [
                'name'      => $gearboxName,
                'is_active' => true,
                // compatible_vehicles se llena si existe el dato (se usa la
                // primera fila con valor no vacío)
            ]
        );

        // Actualizar compatible_vehicles si aún no lo tiene
        $vehiclesFromRow = trim($firstRow['modelos_vehiculo'] ?? '');
        if (! $gearbox->compatible_vehicles && $vehiclesFromRow !== '') {
            $gearbox->update(['compatible_vehicles' => $vehiclesFromRow]);
        }

        // NOTA: extra_field_1 y extra_field_2 de gearbox NO se tocan aquí.
        // Son campos manuales que se gestionan desde el admin.

        // ── 3. Product base (1 por grupo) ───────────────────────────────────
        $product = $this->findOrCreateProduct($gearbox, $firstRow);

        // ── 4. ProductItems (1 por fila del Excel) ──────────────────────────
        foreach ($rows as $index => $row) {
            try {
                $this->upsertProductItem($product, $row);
            } catch (\Throwable $e) {
                $this->errors[] = "[Grupo {$groupKey} / fila #{$index}] " . $e->getMessage();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCTO BASE — findOrCreateProduct
    |--------------------------------------------------------------------------
    */

    /**
     * Busca un producto existente por (gearbox_id, figure, code_gs).
     * Si no existe, lo crea.
     *
     * Lógica de búsqueda (de más específico a más general):
     *   1. gearbox_id + code_gs (si code_gs no está vacío) — primary key real
     *   2. gearbox_id + figure  (si figure no está vacío y code_gs vacío)
     *   3. Producto nuevo (si ambos vacíos)
     */
    private function findOrCreateProduct(Gearbox $gearbox, array $firstRow): Product
    {
        $figure  = trim($firstRow['fig']    ?? '') ?: null;
        $codeGs  = trim($firstRow['cod_gs'] ?? '') ?: null;
        $oemCode = trim($firstRow['codigo_oem'] ?? '');

        $nombreEs = trim($firstRow['nombre']     ?? '') ?: null;
        $descEs   = trim($firstRow['descripcion'] ?? '') ?: null;

        // ── Búsqueda ──────────────────────────────────────────────────────
        $product = null;

        if ($codeGs !== null) {
            // code_gs es el identificador principal del negocio
            $product = Product::where('gearbox_id', $gearbox->id)
                               ->where('code_gs', $codeGs)
                               ->first();
        } elseif ($figure !== null) {
            // Solo figura disponible
            $product = Product::where('gearbox_id', $gearbox->id)
                               ->where('figure', $figure)
                               ->whereNull('code_gs')
                               ->first();
        } else {
            // Sin identificadores de negocio: buscar via item por oem_code
            if ($oemCode !== '') {
                $product = Product::where('gearbox_id', $gearbox->id)
                    ->whereHas('items', fn ($q) => $q->where('oem_code', $oemCode))
                    ->first();
            }
        }

        // ── Creación ──────────────────────────────────────────────────────
        if (! $product) {
            $slugBase = $codeGs ?? $figure ?? $oemCode ?? ('product-' . now()->timestamp);

            $product = Product::create([
                'gearbox_id'  => $gearbox->id,
                'figure'      => $figure,
                'code_gs'     => $codeGs,
                'name'        => ['es' => $nombreEs, 'en' => null, 'pt' => null],
                'description' => ['es' => $descEs,   'en' => null, 'pt' => null],
                'slug'        => $this->generateUniqueSlug($slugBase),
                'is_active'   => true,
            ]);

            $this->created++;
            return $product;
        }

        // ── Actualización parcial (no sobreescribir traducciones manuales) ─
        $updates = [];

        if ($figure !== null && $product->figure === null) {
            $updates['figure'] = $figure;
        }

        if ($nombreEs && empty($product->name['es'])) {
            $name       = $product->name ?? [];
            $name['es'] = $nombreEs;
            $updates['name'] = $name;
        }

        if ($descEs && empty($product->description['es'])) {
            $desc       = $product->description ?? [];
            $desc['es'] = $descEs;
            $updates['description'] = $desc;
        }

        if (! empty($updates)) {
            $product->update($updates);
        }

        $this->updated++;
        return $product;
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT ITEM — upsertProductItem
    |--------------------------------------------------------------------------
    */

    /**
     * Crea o actualiza un ProductItem para la fila dada.
     *
     * Clave de upsert: (product_id, oem_code)
     * Si oem_code es null → siempre crea (no hay clave única para upsert).
     */
    private function upsertProductItem(Product $product, array $row): void
    {
        $oemCode = trim($row['codigo_oem'] ?? '') ?: null;

        // ── application_type ─────────────────────────────────────────────
        // Regla de negocio:
        //   modelos_vehiculo vacío  → genérico (aplica a toda la caja)
        //   modelos_vehiculo con valor → específico (aplica a esos modelos)
        $modelos = trim($row['modelos_vehiculo'] ?? '');

        if ($modelos === '') {
            $application     = null;
            $applicationType = 'generic';
        } else {
            $application     = $modelos;
            $applicationType = 'specific';
        }

        $notas = trim($row['notas'] ?? '') ?: null;

        // ── extra_data: columnas dinámicas + referencias ──────────────────
        $extraData = $this->buildExtraData($row);

        // ── Upsert ────────────────────────────────────────────────────────
        $itemData = [
            'application'      => $application,
            'application_type' => $applicationType,
            'notes'            => $notas,
            'extra_data'       => ! empty($extraData) ? $extraData : null,
        ];

        if ($oemCode !== null) {
            // Buscar item existente por product + oem_code
            $existing = ProductItem::where('product_id', $product->id)
                                   ->where('oem_code', $oemCode)
                                   ->first();

            if ($existing) {
                $existing->update($itemData);
                $this->itemsUpdated++;
            } else {
                ProductItem::create(array_merge(
                    ['product_id' => $product->id, 'oem_code' => $oemCode],
                    $itemData
                ));
                $this->itemsCreated++;
            }
        } else {
            // Sin oem_code → siempre crear (no hay forma de identificar el item)
            ProductItem::create(array_merge(
                ['product_id' => $product->id, 'oem_code' => null],
                $itemData
            ));
            $this->itemsCreated++;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXTRA DATA — columnas dinámicas + referencias
    |--------------------------------------------------------------------------
    */

    /**
     * Construye el JSON extra_data para un ProductItem.
     *
     * Incluye:
     *   - Columnas dinámicas (no en KNOWN_COLUMNS): G60, G85, etc. → bool
     *   - referencia_original  → string|null
     *   - referencias_adicionales → array|null
     *
     * Interpretación de valores en columnas dinámicas:
     *   "x", "si", "yes", "s", "1", "true" → true
     *   Todo lo demás (incluido vacío)       → false
     */
    private function buildExtraData(array $row): array
    {
        $extra = [];

        // ── Columnas dinámicas (G60, G85, G3/36, etc.) ───────────────────
        foreach ($row as $col => $rawValue) {
            if (in_array($col, self::KNOWN_COLUMNS, true)) {
                continue; // columna conocida → no es dinámica
            }

            $normalizedValue = strtolower(trim((string) $rawValue));
            $extra[$col]     = in_array($normalizedValue, ['x', 'si', 'yes', 's', '1', 'true'], true);
        }

        // ── Referencia original (columna conocida → extra_data) ───────────
        $refOriginal = trim($row['referencia_original'] ?? '');
        if ($refOriginal !== '') {
            $extra['referencia_original'] = $refOriginal;
        }

        // ── Referencias adicionales (string separado por ";") ─────────────
        $refsAdicionales = trim($row['referencias_adicionales'] ?? '');
        if ($refsAdicionales !== '') {
            $refs = array_values(
                array_filter(
                    array_map('trim', explode(';', $refsAdicionales))
                )
            );
            if (! empty($refs)) {
                $extra['referencias_adicionales'] = $refs;
            }
        }

        return $extra;
    }

    /*
    |--------------------------------------------------------------------------
    | SLUG ÚNICO
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(string $base): string
    {
        $slug      = Str::slug($base) ?: 'product';
        $candidate = $slug;
        $counter   = 2;

        while (Product::where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $counter++;
        }

        return $candidate;
    }
}
