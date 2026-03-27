<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;

/**
 * CatalogProductsImport — importador para el formato catálogo del cliente.
 *
 * ══ FORMATO DEL EXCEL ═══════════════════════════════════════════════════════
 *
 *  Fila 1: ["Tipo", " Mercedes Benz", null, null, "G-85/60-6", null]
 *           col B = marca,  primera celda no-vacía a partir de col C = gearbox
 *
 *  Fila 2: ["Modelos:", null, "L1218/1518/...", null, null, null]
 *           primera celda no-vacía = vehículos compatibles → gearbox.compatible_vehicles
 *
 *  Fila 3: ["Fig.", "Cod. GS", "Código OEM", "Descripción", "G60", "G85", …]
 *           = cabeceras reales; columnas a partir del índice 4 = variantes de caja
 *
 *  Fila 4+: datos del catálogo
 *
 * ══ MODELO CORRECTO ══════════════════════════════════════════════════════════
 *
 *  1 PRODUCTO (code_gs) → múltiples OEM → múltiples aplicaciones
 *
 *  products:       code_gs, name, gearbox_id, figure
 *  product_items:  product_id, oem_code, application ("G60"/"G85"/null), figure
 *
 * ══ LÓGICA DE ITEMS ══════════════════════════════════════════════════════════
 *
 *  Cada fila del Excel puede generar 1 o N ProductItems:
 *
 *  Si G60=X y G85=X:
 *    → ProductItem 1: oem_code=..., application="G60", figure=...
 *    → ProductItem 2: oem_code=..., application="G85", figure=...
 *
 *  Si solo G60=X:
 *    → ProductItem 1: oem_code=..., application="G60", figure=...
 *
 *  Si ninguna variante marcada (o no hay columnas de variantes):
 *    → ProductItem 1: oem_code=..., application=null, application_type='generic'
 *
 *  ⚠️ "si llega vacío no debe romper" — la ausencia de variantes/modelos
 *  es un dato válido, no un error.
 *
 * ══ AGRUPACIÓN ═══════════════════════════════════════════════════════════════
 *
 *  Clave de producto: code_gs (si existe) → todas las filas con el mismo
 *  code_gs comparten el mismo Product base.
 *  Sin code_gs: fallback por oem_code (cada OEM = producto independiente).
 *
 * ══ UPSERT ═══════════════════════════════════════════════════════════════════
 *
 *  ProductItem: clave de upsert = (product_id, oem_code, application)
 *  Permite reimportar el mismo archivo sin duplicar datos.
 *
 * ══ QUÉ NO HACE ══════════════════════════════════════════════════════════════
 *
 *  ✗ NO toca gearbox.extra_field_1 / extra_field_2  (campos manuales)
 *  ✗ NO sobreescribe compatible_vehicles si ya existe
 */
class CatalogProductsImport implements ToArray
{
    // ── Contadores públicos ────────────────────────────────────────────────
    public int   $created      = 0;
    public int   $updated      = 0;
    public int   $itemsCreated = 0;
    public int   $itemsUpdated = 0;
    public array $errors       = [];

    // ── Índices de columnas fijas ──────────────────────────────────────────
    private const IDX_FIG   = 0;   // Figura del catálogo
    private const IDX_CODGS = 1;   // Código GS (identificador del producto)
    private const IDX_OEM   = 2;   // Código OEM
    private const IDX_DESC  = 3;   // Descripción / nombre
    // Índice 4+ → variantes dinámicas (G60, G85, G3/36, …)

    /*
    |--------------------------------------------------------------------------
    | ENTRY POINT
    |--------------------------------------------------------------------------
    */

    public function array(array $allRows): void
    {
        if (count($allRows) < 4) {
            $this->errors[] = 'El archivo no tiene suficientes filas (mínimo 4).';
            return;
        }

        // ── Fila 1 (índice 0): marca + gearbox ──────────────────────────────
        $metaRow   = array_values($allRows[0]);
        $brandName = trim((string) ($metaRow[1] ?? ''));

        if ($brandName === '') {
            $this->errors[] = 'No se encontró nombre de marca en la celda B1.';
            return;
        }

        $gearboxName = '';
        for ($i = 2, $max = count($metaRow); $i < $max; $i++) {
            $cell = trim((string) ($metaRow[$i] ?? ''));
            if ($cell !== '') {
                $gearboxName = $cell;
                break;
            }
        }

        if ($gearboxName === '') {
            $this->errors[] = 'No se encontró nombre de caja de cambios en la fila 1.';
            return;
        }

        // ── Fila 2 (índice 1): vehículos compatibles ─────────────────────────
        $vehiclesRow        = array_values($allRows[1]);
        $compatibleVehicles = '';

        for ($i = 1, $max = count($vehiclesRow); $i < $max; $i++) {
            $cell = trim((string) ($vehiclesRow[$i] ?? ''));
            if ($cell !== '') {
                $compatibleVehicles = $cell;
                break;
            }
        }

        // ── Fila 3 (índice 2): cabeceras reales ──────────────────────────────
        // Las columnas a partir del índice 4 son variantes de caja.
        // Se guarda el nombre ORIGINAL (tal cual aparece en el Excel): "G60", "G85"
        $headerRow      = array_values($allRows[2]);
        $variantHeaders = [];   // idx → "G60" / "G85" / ...

        for ($i = 4, $max = count($headerRow); $i < $max; $i++) {
            $header = trim((string) ($headerRow[$i] ?? ''));
            if ($header !== '') {
                $variantHeaders[$i] = $header;
            }
        }

        // ── Brand + Gearbox ──────────────────────────────────────────────────
        $brand = Brand::firstOrCreate(
            ['slug' => Str::slug($brandName)],
            [
                'name'      => ['es' => $brandName, 'en' => $brandName, 'pt' => $brandName],
                'is_active' => true,
            ]
        );

        $gearbox = Gearbox::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => Str::slug($gearboxName)],
            ['name' => $gearboxName, 'is_active' => true]
        );

        if ($compatibleVehicles !== '' && ! $gearbox->compatible_vehicles) {
            $gearbox->update(['compatible_vehicles' => $compatibleVehicles]);
        }

        // ── Filas de datos (índice 3+) ───────────────────────────────────────
        $dataRows = array_slice($allRows, 3);
        $groups   = $this->groupDataRows($dataRows);

        foreach ($groups as $groupKey => $groupRows) {
            try {
                $this->processGroup($groupKey, $groupRows, $gearbox, $variantHeaders);
            } catch (\Throwable $e) {
                $this->errors[] = "[Grupo {$groupKey}] " . $e->getMessage();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AGRUPACIÓN POR PRODUCTO
    |--------------------------------------------------------------------------
    */

    /**
     * Agrupa las filas de datos por clave de producto.
     *
     * Clave principal: code_gs (ej: "800", "826")
     * Fallback si code_gs es null/"-": oem_code ("__oem__694-262-0002")
     *
     * Filas sin identificador → ignoradas silenciosamente.
     */
    private function groupDataRows(array $dataRows): array
    {
        $groups = [];

        foreach ($dataRows as $row) {
            $row   = array_values($row);
            $codGs = $this->normalizeId($row[self::IDX_CODGS] ?? null);
            $oem   = trim((string) ($row[self::IDX_OEM] ?? ''));

            if ($codGs !== null) {
                $groupKey = "gs_{$codGs}";
            } elseif ($oem !== '') {
                $groupKey = "__oem__{$oem}";
            } else {
                continue; // Sin identificador → omitir
            }

            $groups[$groupKey][] = $row;
        }

        return $groups;
    }

    /*
    |--------------------------------------------------------------------------
    | PROCESAMIENTO DE GRUPO (1 Product + N ProductItems)
    |--------------------------------------------------------------------------
    */

    private function processGroup(
        string  $groupKey,
        array   $groupRows,
        Gearbox $gearbox,
        array   $variantHeaders
    ): void {
        $firstRow = $groupRows[0];

        $figure  = $this->normalizeId($firstRow[self::IDX_FIG]   ?? null);
        $codeGs  = $this->normalizeId($firstRow[self::IDX_CODGS] ?? null);
        $desc    = trim((string) ($firstRow[self::IDX_DESC] ?? ''));
        $oem     = trim((string) ($firstRow[self::IDX_OEM]  ?? '')) ?: null;

        // 1 Product por grupo ─────────────────────────────────────────────────
        $product = $this->findOrCreateProduct($gearbox, $figure, $codeGs, $desc, $oem);

        // N ProductItems (1 por fila × 1 por variante activa) ─────────────────
        foreach ($groupRows as $index => $row) {
            try {
                $this->processRowItems($product, $row, $variantHeaders);
            } catch (\Throwable $e) {
                $this->errors[] = "[Grupo {$groupKey} / fila #{$index}] " . $e->getMessage();
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCT BASE — findOrCreateProduct
    |--------------------------------------------------------------------------
    */

    private function findOrCreateProduct(
        Gearbox $gearbox,
        ?string $figure,
        ?string $codeGs,
        string  $desc,
        ?string $oemFallback = null
    ): Product {
        // Búsqueda: code_gs → figure → oem_code en items (fallback)
        $product = null;

        if ($codeGs !== null) {
            $product = Product::where('gearbox_id', $gearbox->id)
                               ->where('code_gs', $codeGs)
                               ->first();
        } elseif ($figure !== null) {
            $product = Product::where('gearbox_id', $gearbox->id)
                               ->where('figure', $figure)
                               ->whereNull('code_gs')
                               ->first();
        } elseif ($oemFallback !== null) {
            // Sin code_gs ni figure → buscar via oem_code en product_items
            $product = Product::where('gearbox_id', $gearbox->id)
                               ->whereHas('items', fn ($q) => $q->where('oem_code', $oemFallback))
                               ->first();
        }

        // Creación ─────────────────────────────────────────────────────────────
        if (! $product) {
            $slugBase = $codeGs ?? $figure ?? ('product-' . now()->timestamp);

            $product = Product::create([
                'gearbox_id'  => $gearbox->id,
                'figure'      => $figure,
                'code_gs'     => $codeGs,
                'name'        => ['es' => null, 'en' => null, 'pt' => null],
                'description' => ['es' => $desc ?: null, 'en' => null, 'pt' => null],
                'slug'        => $this->generateUniqueSlug($slugBase),
                'is_active'   => true,
            ]);

            $this->created++;
            return $product;
        }

        // Actualización parcial ────────────────────────────────────────────────
        $updates = [];

        if ($figure !== null && $product->figure === null) {
            $updates['figure'] = $figure;
        }

        if ($desc !== '' && empty($product->description['es'] ?? null)) {
            $description       = $product->description ?? [];
            $description['es'] = $desc;
            $updates['description'] = $description;
        }

        if (! empty($updates)) {
            $product->update($updates);
        }

        $this->updated++;
        return $product;
    }

    /*
    |--------------------------------------------------------------------------
    | ITEMS — 1 item por variante activa
    |--------------------------------------------------------------------------
    */

    /**
     * Procesa una fila del Excel y crea/actualiza los ProductItems correspondientes.
     *
     * Regla principal:
     *   · Por cada variante marcada (G60=X, G85=X) → 1 ProductItem con application=variante
     *   · Si ninguna variante está marcada (o no hay columnas de variantes):
     *     → 1 ProductItem con application=null (application_type='generic')
     *
     * Esto garantiza que una fila sin columnas de variantes NO rompa la importación.
     */
    private function processRowItems(
        Product $product,
        array   $row,
        array   $variantHeaders
    ): void {
        $oem    = trim((string) ($row[self::IDX_OEM] ?? '')) ?: null;
        $figure = $this->normalizeId($row[self::IDX_FIG] ?? null);

        // Detectar variantes activas en esta fila
        $activeVariants = [];

        foreach ($variantHeaders as $idx => $variantName) {
            $cell = trim((string) ($row[$idx] ?? ''));
            if ($this->isTruthy($cell)) {
                $activeVariants[] = $variantName; // "G60", "G85", etc.
            }
        }

        if (empty($activeVariants)) {
            // Sin variantes → 1 item genérico (no rompe la importación)
            $this->upsertItem($product, $oem, $figure, null);
        } else {
            // 1 item por variante activa
            foreach ($activeVariants as $variantName) {
                $this->upsertItem($product, $oem, $figure, $variantName);
            }
        }
    }

    /**
     * Crea o actualiza un ProductItem.
     *
     * Clave de upsert: (product_id, oem_code, application)
     *   - Mismo producto + mismo OEM + misma variante → actualizar
     *   - Caso nuevo → crear
     *
     * Si oem_code es null → siempre crea (sin clave para deduplicar).
     */
    private function upsertItem(
        Product $product,
        ?string $oem,
        ?string $figure,
        ?string $application
    ): void {
        $applicationType = $application !== null ? 'specific' : 'generic';

        $itemData = [
            'figure'           => $figure,
            'application'      => $application,
            'application_type' => $applicationType,
            'notes'            => null,
            'extra_data'       => null,
        ];

        if ($oem !== null) {
            // Buscar item existente por (product_id, oem_code, application)
            $existing = ProductItem::where('product_id', $product->id)
                                   ->where('oem_code', $oem)
                                   ->where(function ($q) use ($application) {
                                       $application === null
                                           ? $q->whereNull('application')
                                           : $q->where('application', $application);
                                   })
                                   ->first();

            if ($existing) {
                $existing->update($itemData);
                $this->itemsUpdated++;
            } else {
                ProductItem::create(array_merge(
                    ['product_id' => $product->id, 'oem_code' => $oem],
                    $itemData
                ));
                $this->itemsCreated++;
            }
        } else {
            // Sin OEM → siempre crear
            ProductItem::create(array_merge(
                ['product_id' => $product->id, 'oem_code' => null],
                $itemData
            ));
            $this->itemsCreated++;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Normaliza valores de fig / cod_gs:
     *   null, "", "-", "   " → null
     *   Cualquier otro valor → string trimmed
     */
    private function normalizeId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $str = trim((string) $value);
        return ($str === '' || $str === '-') ? null : $str;
    }

    /**
     * "X", "x", "si", "yes", "1", "true", "s" → true
     * Vacío / null / cualquier otra cosa        → false
     */
    private function isTruthy(string $value): bool
    {
        return in_array(strtolower($value), ['x', 'si', 'yes', 's', '1', 'true'], true);
    }

    private function generateUniqueSlug(string $base): string
    {
        $slug      = Str::slug((string) $base) ?: 'product';
        $candidate = $slug;
        $counter   = 2;

        while (Product::where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $counter++;
        }

        return $candidate;
    }
}
