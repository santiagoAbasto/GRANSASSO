<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use App\Models\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProductsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors  = [];
    public int   $created = 0;
    public int   $updated = 0;

    /**
     * Columnas esperadas en el Excel:
     *
     * marca | modelo_caja | modelos_vehiculo | fig | cod_gs | codigo_oem
     * nombre | descripcion | referencia_original | referencias_adicionales
     * aplicaciones | [variante_1] | [variante_2] | ...
     *
     * Las columnas de variantes son dinámicas (G60, G85, G3/36, etc.)
     * y se detectan automáticamente como todo lo que no sea una columna conocida.
     */
    private const KNOWN_COLUMNS = [
        'marca', 'modelo_caja', 'modelos_vehiculo', 'fig', 'cod_gs',
        'codigo_oem', 'nombre', 'descripcion', 'referencia_original',
        'referencias_adicionales', 'aplicaciones',
    ];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            try {
                $this->processRow($row->toArray(), $rowIndex + 2);
            } catch (\Throwable $e) {
                $this->errors[] = "Fila " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }
    }

    private function processRow(array $row, int $line): void
    {
        // Normalizar claves: minúsculas, sin espacios extra
        $row = array_combine(
            array_map(fn ($k) => strtolower(trim($k)), array_keys($row)),
            array_values($row)
        );

        $brandName   = trim($row['marca'] ?? '');
        $gearboxName = trim($row['modelo_caja'] ?? '');
        $codeOem     = trim($row['codigo_oem'] ?? '');

        if (!$brandName || !$gearboxName || !$codeOem) {
            $this->errors[] = "Fila {$line}: marca, modelo_caja y codigo_oem son obligatorios.";
            return;
        }

        // 1. Brand — buscar o crear
        $brand = Brand::firstOrCreate(
            ['slug' => Str::slug($brandName)],
            [
                'name'      => ['es' => $brandName, 'en' => $brandName, 'pt' => $brandName],
                'is_active' => true,
            ]
        );

        // 2. Gearbox — buscar o crear
        $gearbox = Gearbox::firstOrCreate(
            ['brand_id' => $brand->id, 'slug' => Str::slug($gearboxName)],
            [
                'name'                => $gearboxName,
                'compatible_vehicles' => trim($row['modelos_vehiculo'] ?? ''),
                'is_active'           => true,
            ]
        );

        // Si ya existía el gearbox pero le falta compatible_vehicles, actualizar
        if (!$gearbox->compatible_vehicles && !empty($row['modelos_vehiculo'])) {
            $gearbox->update(['compatible_vehicles' => trim($row['modelos_vehiculo'])]);
        }

        // 3. Product — crear o actualizar por code_oem
        $nombreEs = trim($row['nombre'] ?? '');
        $descEs   = trim($row['descripcion'] ?? '');
        $refs     = trim($row['referencias_adicionales'] ?? '');

        $productData = [
            'gearbox_id'              => $gearbox->id,
            'figure'                  => trim($row['fig'] ?? '') ?: null,
            'cod_gs'                  => trim($row['cod_gs'] ?? '') ?: null,
            'code_oem'                => $codeOem,
            'nombre'                  => ['es' => $nombreEs ?: null, 'en' => null, 'pt' => null],
            'referencia_original'     => trim($row['referencia_original'] ?? '') ?: null,
            'referencias_adicionales' => $refs
                ? array_values(array_filter(array_map('trim', explode(';', $refs))))
                : null,
            'description'             => ['es' => $descEs ?: null, 'en' => null, 'pt' => null],
            'is_active'               => true,
        ];

        $product = Product::updateOrCreate(
            ['code_oem' => $codeOem],
            $productData
        );

        $product->wasRecentlyCreated ? $this->created++ : $this->updated++;

        // 4. Aplicaciones (pipe-separated)
        $appsRaw = trim($row['aplicaciones'] ?? '');
        if ($appsRaw) {
            foreach (array_filter(array_map('trim', explode('|', $appsRaw))) as $appName) {
                Application::firstOrCreate([
                    'gearbox_id' => $gearbox->id,
                    'name'       => $appName,
                ]);
                $app = Application::where('gearbox_id', $gearbox->id)->where('name', $appName)->first();
                $product->applications()->syncWithoutDetaching([$app->id]);
            }
        }

        // 5. Variantes dinámicas — columnas que no son conocidas y tienen "x" o están llenas
        $variantColumns = array_filter(
            array_keys($row),
            fn ($k) => !in_array($k, self::KNOWN_COLUMNS)
        );

        foreach ($variantColumns as $variantName) {
            $value = strtolower(trim((string) ($row[$variantName] ?? '')));
            $isActive = in_array($value, ['x', 'si', 'yes', 's', '1', 'true']);

            if ($isActive) {
                $product->versions()->firstOrCreate(
                    ['version' => $variantName],
                    ['is_available' => true]
                );
            }
        }
    }
}
