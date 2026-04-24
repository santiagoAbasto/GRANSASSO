<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use App\Models\ProductItem;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

/**
 * CatalogProductsImport — importador para el formato catálogo del cliente.
 *
 * Soporta:
 * - Archivos con una o varias hojas.
 * - Encabezados superiores flexibles por hoja.
 * - Preservación del contexto del catálogo completo en gearbox.catalog_meta
 *   y product_items.extra_data.
 *
 * Estructura esperada por hoja:
 * - Fila 1: Tipo / Marca / datos superiores de caja
 * - Fila 2: Aplicación / vehículos compatibles / notas laterales (ej: Marchas)
 * - Fila 3: Fig / # GSS / # OEM / Descripción / variantes dinámicas
 * - Fila 4+: datos
 */
class CatalogProductsImport implements ToArray, WithEvents
{
    public int $created = 0;
    public int $updated = 0;
    public int $itemsCreated = 0;
    public int $itemsUpdated = 0;
    public array $errors = [];

    private const IDX_FIG = 0;
    private const IDX_CODGS = 1;
    private const IDX_OEM = 2;
    private const IDX_DESC = 3;

    private ?string $currentSheetName = null;

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $this->currentSheetName = $this->normalizeText((string) $event->sheet->getTitle());
            },
        ];
    }

    public function array(array $allRows): void
    {
        $sheetLabel = $this->currentSheetName ? "Hoja {$this->currentSheetName}" : 'Hoja sin nombre';

        if (count($allRows) < 4) {
            $this->errors[] = "[{$sheetLabel}] El archivo no tiene suficientes filas (mínimo 4).";
            return;
        }

        $blocks = $this->splitCatalogBlocks($allRows);

        foreach ($blocks as $blockIndex => $block) {
            $blockRows = $block['rows'];
            $blockStartRow = $block['start_row'];
            $context = $this->extractSheetContext(array_values($blockRows));
            $blockLabel = "{$sheetLabel} / bloque {$blockIndex} / fila {$blockStartRow}";

            if ($context['brand_name'] === '') {
                $this->errors[] = "[{$blockLabel}] No se encontró nombre de marca en el encabezado.";
                continue;
            }

            if ($context['gearbox_name'] === '') {
                $this->errors[] = "[{$blockLabel}] No se pudo determinar la caja o familia del catálogo.";
                continue;
            }

            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($context['brand_name'])],
                [
                    'name' => [
                        'es' => $context['brand_name'],
                        'en' => $context['brand_name'],
                        'pt' => $context['brand_name'],
                    ],
                    'is_active' => true,
                ]
            );

            $gearbox = $this->upsertGearboxFromContext($brand, $context);
            $dataRows = $this->prepareDataRows(array_slice($blockRows, 3, null, true));
            $groups = $this->groupDataRows($dataRows);

            foreach ($groups as $groupKey => $groupRows) {
                try {
                    $this->processGroup($groupKey, $groupRows, $gearbox, $context['variant_headers']);
                } catch (\Throwable $e) {
                    $this->errors[] = "[{$blockLabel} / grupo {$groupKey}] {$e->getMessage()}";
                }
            }
        }
    }

    private function splitCatalogBlocks(array $allRows): array
    {
        $blocks = [];
        $currentStartIndex = null;
        $rowIndexes = array_keys($allRows);
        $lastIndex = count($rowIndexes) - 1;

        foreach ($rowIndexes as $position => $rowIndex) {
            $row = $allRows[$rowIndex] ?? [];

            if (! $this->isTypeMarkerRow($row)) {
                continue;
            }

            if ($currentStartIndex !== null) {
                $length = $position - $currentStartIndex;

                if ($length >= 4) {
                    $rows = array_slice($allRows, $currentStartIndex, $length, true);
                    $blocks[] = [
                        'start_row' => array_keys($rows)[0] + 1,
                        'rows' => $rows,
                    ];
                }
            }

            $currentStartIndex = $position;

            if ($position === $lastIndex) {
                $rows = array_slice($allRows, $currentStartIndex, null, true);
                if (count($rows) >= 4) {
                    $blocks[] = [
                        'start_row' => array_keys($rows)[0] + 1,
                        'rows' => $rows,
                    ];
                }
            }
        }

        if ($currentStartIndex !== null && $lastIndex >= $currentStartIndex) {
            $lastBlock = end($blocks);
            $lastBlockStart = $lastBlock['start_row'] ?? null;
            $expectedStart = $rowIndexes[$currentStartIndex] + 1;

            if ($lastBlockStart !== $expectedStart) {
                $rows = array_slice($allRows, $currentStartIndex, null, true);
                if (count($rows) >= 4) {
                    $blocks[] = [
                        'start_row' => array_keys($rows)[0] + 1,
                        'rows' => $rows,
                    ];
                }
            }
        }

        if ($blocks === []) {
            $blocks[] = [
                'start_row' => 1,
                'rows' => $allRows,
            ];
        }

        return $blocks;
    }

    private function isTypeMarkerRow(array $row): bool
    {
        $values = array_values($row);
        $firstCell = Str::lower($this->normalizeText((string) ($values[0] ?? '')));

        return $firstCell === 'tipo';
    }

    private function extractSheetContext(array $allRows): array
    {
        $metaRow = $this->normalizeRow($allRows[0] ?? []);
        $applicationRow = $this->normalizeRow($allRows[1] ?? []);
        $headerRow = $this->normalizeRow($allRows[2] ?? []);

        $brandName = $metaRow[1] ?? '';
        if ($brandName === '') {
            $brandName = $this->firstNonEmptyValue($metaRow, 1) ?? '';
        }

        $topMetaValues = $this->extractNonEmptyValues($metaRow, 1);
        $brandRemoved = false;
        $topMetaValues = array_values(array_filter($topMetaValues, function (string $value) use ($brandName, &$brandRemoved) {
            if (! $brandRemoved && $brandName !== '' && $value === $brandName) {
                $brandRemoved = true;
                return false;
            }

            return true;
        }));

        $gearboxName = $topMetaValues !== [] ? implode(' / ', $topMetaValues) : '';

        $applicationValues = $this->extractNonEmptyValues($applicationRow, 1);
        $primaryApplication = $applicationValues[0] ?? null;
        $secondaryApplication = array_values(array_slice($applicationValues, 1));

        $applicationPromotedToGearbox = false;
        if ($gearboxName === '' && $primaryApplication) {
            $gearboxName = $primaryApplication;
            $primaryApplication = null;
            $applicationPromotedToGearbox = true;
        }

        if ($gearboxName === '' && $this->currentSheetName) {
            $gearboxName = $this->currentSheetName;
        }

        $variantHeaders = [];
        for ($i = 4, $max = count($headerRow); $i < $max; $i++) {
            $header = $headerRow[$i] ?? '';
            if ($header !== '') {
                $variantHeaders[$i] = $header;
            }
        }

        $gearboxMeta = [
            'sheet_name' => $this->currentSheetName,
            'source_sheets' => $this->cleanList([$this->currentSheetName]),
            'brand_name' => $brandName,
            'gearbox_name' => $gearboxName,
            'top_meta' => $topMetaValues,
            'application_primary' => $primaryApplication,
            'application_secondary' => $secondaryApplication,
            'application_promoted_to_gearbox' => $applicationPromotedToGearbox,
            'variant_headers' => array_values($variantHeaders),
            'labels' => [
                'meta' => $metaRow[0] ?? null,
                'application' => $applicationRow[0] ?? null,
            ],
            'column_headers' => [
                'figure' => $headerRow[self::IDX_FIG] ?? null,
                'code_gs' => $headerRow[self::IDX_CODGS] ?? null,
                'oem_code' => $headerRow[self::IDX_OEM] ?? null,
                'description' => $headerRow[self::IDX_DESC] ?? null,
            ],
            'raw_rows' => [
                'row_1' => $metaRow,
                'row_2' => $applicationRow,
                'row_3' => $headerRow,
            ],
            'marchas' => $this->extractMarchValue($secondaryApplication),
        ];

        return [
            'brand_name' => $brandName,
            'gearbox_name' => $gearboxName,
            'compatible_vehicles' => $primaryApplication,
            'variant_headers' => $variantHeaders,
            'catalog_meta' => $gearboxMeta,
        ];
    }

    private function upsertGearboxFromContext(Brand $brand, array $context): Gearbox
    {
        $gearbox = Gearbox::firstOrCreate(
            [
                'brand_id' => $brand->id,
                'slug' => Str::slug($context['gearbox_name']),
            ],
            [
                'name' => $context['gearbox_name'],
                'is_active' => true,
            ]
        );

        $updates = [];

        if ($gearbox->name !== $context['gearbox_name']) {
            $updates['name'] = $context['gearbox_name'];
        }

        if (! empty($context['compatible_vehicles']) && $gearbox->compatible_vehicles !== $context['compatible_vehicles']) {
            $updates['compatible_vehicles'] = $context['compatible_vehicles'];
        }

        $existingCatalogMeta = is_array($gearbox->catalog_meta ?? null) ? $gearbox->catalog_meta : [];
        $mergedCatalogMeta = $this->mergeCatalogMeta($existingCatalogMeta, $context['catalog_meta']);

        if (($gearbox->catalog_meta ?? null) !== $mergedCatalogMeta) {
            $updates['catalog_meta'] = $mergedCatalogMeta;
        }

        if (! $gearbox->is_active) {
            $updates['is_active'] = true;
        }

        if ($updates !== []) {
            $gearbox->update($updates);
        }

        return $gearbox;
    }

    private function prepareDataRows(array $dataRows): array
    {
        $preparedRows = [];
        $currentEntryIndex = null;

        foreach ($dataRows as $rowIndex => $row) {
            $values = array_values($row);
            $figure = $this->normalizeId($values[self::IDX_FIG] ?? null);
            $codGs = $this->normalizeId($values[self::IDX_CODGS] ?? null);
            $oem = $this->normalizeText((string) ($values[self::IDX_OEM] ?? ''));
            $description = $this->normalizeText((string) ($values[self::IDX_DESC] ?? ''));

            $isCompletelyEmpty = $figure === null
                && $codGs === null
                && $oem === ''
                && $description === '';

            if ($isCompletelyEmpty) {
                continue;
            }

            $isContinuationRow = $figure === null
                && $codGs === null
                && $oem === ''
                && $description !== '';

            if ($isContinuationRow && $currentEntryIndex !== null) {
                $currentDescription = trim((string) ($preparedRows[$currentEntryIndex]['values'][self::IDX_DESC] ?? ''));
                $preparedRows[$currentEntryIndex]['values'][self::IDX_DESC] = $currentDescription === ''
                    ? $description
                    : rtrim($currentDescription) . "\n" . $description;
                $preparedRows[$currentEntryIndex]['source_rows'][] = $rowIndex + 1;

                continue;
            }

            $preparedRows[] = [
                'values' => $values,
                'sheet_row' => $rowIndex + 1,
                'source_rows' => [$rowIndex + 1],
            ];
            $currentEntryIndex = array_key_last($preparedRows);
        }

        return $preparedRows;
    }

    private function groupDataRows(array $dataRows): array
    {
        $groups = [];

        foreach ($dataRows as $entry) {
            $values = $entry['values'];
            $codGs = $this->normalizeId($values[self::IDX_CODGS] ?? null);
            $oem = $this->normalizeText((string) ($values[self::IDX_OEM] ?? ''));

            if ($codGs !== null) {
                $groupKey = "gs_{$codGs}";
            } elseif ($oem !== '') {
                $groupKey = "__oem__{$oem}";
            } else {
                continue;
            }

            $groups[$groupKey][] = $entry;
        }

        return $groups;
    }

    private function processGroup(
        string $groupKey,
        array $groupRows,
        Gearbox $gearbox,
        array $variantHeaders
    ): void {
        $firstRow = $groupRows[0]['values'];

        $figure = $this->normalizeId($firstRow[self::IDX_FIG] ?? null);
        $codeGs = $this->normalizeId($firstRow[self::IDX_CODGS] ?? null);
        $desc = $this->normalizeText((string) ($firstRow[self::IDX_DESC] ?? ''));
        $oem = $this->normalizeText((string) ($firstRow[self::IDX_OEM] ?? '')) ?: null;

        $product = $this->findOrCreateProduct($gearbox, $figure, $codeGs, $desc, $oem);

        foreach ($groupRows as $index => $entry) {
            try {
                $this->processRowItems(
                    $product,
                    $entry['values'],
                    $variantHeaders,
                    $entry['sheet_row'],
                    $entry['source_rows'] ?? [$entry['sheet_row']]
                );
            } catch (\Throwable $e) {
                $sheetLabel = $this->currentSheetName ? "Hoja {$this->currentSheetName}" : 'Hoja sin nombre';
                $this->errors[] = "[{$sheetLabel} / grupo {$groupKey} / fila {$entry['sheet_row']}] {$e->getMessage()}";
            }
        }
    }

    private function findOrCreateProduct(
        Gearbox $gearbox,
        ?string $figure,
        ?string $codeGs,
        string $desc,
        ?string $oemFallback = null
    ): Product {
        $product = null;

        if ($codeGs !== null) {
            $product = Product::visibleInGearbox($gearbox->id)
                ->where('code_gs', $codeGs)
                ->first();
        } elseif ($figure !== null) {
            $product = Product::visibleInGearbox($gearbox->id)
                ->where('figure', $figure)
                ->whereNull('code_gs')
                ->first();
        } elseif ($oemFallback !== null) {
            $product = Product::visibleInGearbox($gearbox->id)
                ->whereHas('items', fn ($q) => $q->where('oem_code', $oemFallback))
                ->first();
        }

        if (! $product) {
            $product = $this->findCompatibleProductAcrossCatalog(
                $gearbox,
                $figure,
                $codeGs,
                $oemFallback
            );
        }

        if (! $product) {
            $slugBase = $codeGs ?? $figure ?? ($oemFallback ?: 'product-' . now()->timestamp);

            $product = Product::create([
                'gearbox_id' => $gearbox->id,
                'figure' => $figure,
                'code_gs' => $codeGs,
                'name' => ['es' => null, 'en' => null, 'pt' => null],
                'description' => ['es' => $desc ?: null, 'en' => null, 'pt' => null],
                'slug' => $this->generateUniqueSlug($slugBase),
                'is_active' => true,
            ]);

            $this->created++;
            return $product;
        }

        $this->linkProductToGearbox($product, $gearbox);

        $updates = [];

        if ($figure !== null && $product->figure === null) {
            $updates['figure'] = $figure;
        }

        if ($desc !== '' && empty($product->description['es'] ?? null)) {
            $description = $product->description ?? [];
            $description['es'] = $desc;
            $updates['description'] = $description;
        }

        if ($updates !== []) {
            $product->update($updates);
        }

        $this->updated++;
        return $product;
    }

    private function findCompatibleProductAcrossCatalog(
        Gearbox $gearbox,
        ?string $figure,
        ?string $codeGs,
        ?string $oemFallback
    ): ?Product {
        if ($codeGs === null && $figure === null) {
            return null;
        }

        $query = Product::query()
            ->where(function ($q) use ($gearbox) {
                $q->where('gearbox_id', '!=', $gearbox->id)
                    ->orWhereHas('secondaryGearboxes', fn ($secondary) => $secondary->where('gearboxes.id', '!=', $gearbox->id));
            });

        if ($codeGs !== null) {
            $query->where('code_gs', $codeGs);
        } else {
            $query->whereNull('code_gs');
        }

        if ($figure !== null) {
            $query->where('figure', $figure);
        }

        if ($oemFallback !== null) {
            $query->whereHas('items', fn ($q) => $q->where('oem_code', $oemFallback));
        }

        return $query->orderBy('id')->first();
    }

    private function linkProductToGearbox(Product $product, Gearbox $gearbox): void
    {
        if ((int) $product->gearbox_id === (int) $gearbox->id) {
            return;
        }

        $product->secondaryGearboxes()->syncWithoutDetaching([$gearbox->id]);
    }

    private function processRowItems(
        Product $product,
        array $row,
        array $variantHeaders,
        int $sheetRow,
        array $sourceRows
    ): void {
        $oem = $this->normalizeText((string) ($row[self::IDX_OEM] ?? '')) ?: null;
        $figure = $this->normalizeId($row[self::IDX_FIG] ?? null);

        $activeVariants = [];
        foreach ($variantHeaders as $idx => $variantName) {
            $cell = $this->normalizeText((string) ($row[$idx] ?? ''));
            if ($this->isTruthy($cell)) {
                $activeVariants[] = $variantName;
            }
        }

        $extraData = $this->buildItemExtraData($row, $variantHeaders, $sheetRow, $sourceRows);

        if ($activeVariants === []) {
            $this->upsertItem(
                $product,
                $oem,
                $figure,
                null,
                $extraData
            );

            return;
        }

        foreach ($activeVariants as $variantName) {
            $this->upsertItem(
                $product,
                $oem,
                $figure,
                $variantName,
                $extraData
            );
        }
    }

    private function upsertItem(
        Product $product,
        ?string $oem,
        ?string $figure,
        ?string $application,
        ?array $extraData
    ): void {
        $applicationType = $application !== null ? 'specific' : 'generic';

        $itemData = [
            'figure' => $figure,
            'application' => $application,
            'application_type' => $applicationType,
            'notes' => null,
            'extra_data' => $extraData ?: null,
        ];

        $existing = ProductItem::where('product_id', $product->id)
            ->where(function ($q) use ($oem) {
                $oem === null
                    ? $q->whereNull('oem_code')
                    : $q->where('oem_code', $oem);
            })
            ->where(function ($q) use ($application) {
                $application === null
                    ? $q->whereNull('application')
                    : $q->where('application', $application);
            })
            ->where(function ($q) use ($figure) {
                $figure === null
                    ? $q->whereNull('figure')
                    : $q->where('figure', $figure);
            })
            ->first();

        if ($existing) {
            $existing->update($itemData);
            $this->itemsUpdated++;

            return;
        }

        ProductItem::create(array_merge(
            ['product_id' => $product->id, 'oem_code' => $oem],
            $itemData
        ));
        $this->itemsCreated++;
    }

    private function buildItemExtraData(array $row, array $variantHeaders, int $sheetRow, array $sourceRows): array
    {
        $normalizedRow = $this->normalizeRow($row);
        $variantFlags = [];
        foreach ($variantHeaders as $idx => $variantName) {
            $variantFlags[$variantName] = $this->isTruthy($this->normalizeText((string) ($row[$idx] ?? '')));
        }

        $extraData = $variantFlags;
        $extraData['source_sheet'] = $this->currentSheetName;
        $extraData['source_row'] = $sheetRow;
        $extraData['source_rows'] = array_values(array_unique($sourceRows));
        $extraData['variant_headers'] = array_values($variantHeaders);
        $extraData['catalog_row'] = [
            'sheet_name' => $this->currentSheetName,
            'sheet_row' => $sheetRow,
            'source_rows' => array_values(array_unique($sourceRows)),
            'figure' => $this->normalizeId($row[self::IDX_FIG] ?? null),
            'code_gs' => $this->normalizeId($row[self::IDX_CODGS] ?? null),
            'oem_code' => $this->normalizeText((string) ($row[self::IDX_OEM] ?? '')) ?: null,
            'description' => $this->normalizeText((string) ($row[self::IDX_DESC] ?? '')) ?: null,
            'raw_row' => $normalizedRow,
            'variant_flags' => $variantFlags,
            'variant_headers' => array_values($variantHeaders),
        ];

        return $extraData;
    }

    private function normalizeRow(array $row): array
    {
        return array_map(fn ($value) => $this->normalizeText((string) ($value ?? '')), array_values($row));
    }

    private function extractNonEmptyValues(array $row, int $startIndex = 0): array
    {
        $values = [];

        for ($i = $startIndex, $max = count($row); $i < $max; $i++) {
            $value = $row[$i] ?? '';
            if ($value !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }

    private function firstNonEmptyValue(array $row, int $startIndex = 0): ?string
    {
        foreach ($this->extractNonEmptyValues($row, $startIndex) as $value) {
            return $value;
        }

        return null;
    }

    private function extractMarchValue(array $values): ?string
    {
        foreach ($values as $value) {
            if (str_contains(Str::lower($value), 'marcha')) {
                return $value;
            }
        }

        return null;
    }

    private function normalizeText(string $value): string
    {
        return preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
    }

    private function cleanList(array $values): array
    {
        $cleaned = [];
        $seen = [];

        foreach ($values as $value) {
            $normalized = $this->normalizeText((string) ($value ?? ''));

            if ($normalized === '') {
                continue;
            }

            $key = Str::lower($normalized);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $cleaned[] = $normalized;
        }

        return $cleaned;
    }

    private function mergeCatalogMeta(array $existing, array $incoming): array
    {
        $merged = array_merge($existing, $incoming);

        $merged['source_sheets'] = $this->cleanList(array_merge(
            is_array($existing['source_sheets'] ?? null) ? $existing['source_sheets'] : [],
            [$existing['sheet_name'] ?? null],
            is_array($incoming['source_sheets'] ?? null) ? $incoming['source_sheets'] : [],
            [$incoming['sheet_name'] ?? null]
        ));

        foreach (['top_meta', 'application_secondary', 'variant_headers'] as $key) {
            $merged[$key] = $this->cleanList(array_merge(
                is_array($existing[$key] ?? null) ? $existing[$key] : [],
                is_array($incoming[$key] ?? null) ? $incoming[$key] : []
            ));
        }

        if (! empty($merged['source_sheets'])) {
            $lastSheet = $merged['source_sheets'];
            $merged['sheet_name'] = end($lastSheet) ?: ($incoming['sheet_name'] ?? $existing['sheet_name'] ?? null);
        }

        if (! empty($incoming['raw_rows'])) {
            $merged['raw_rows'] = $incoming['raw_rows'];
        }

        if (empty($merged['marchas'])) {
            $merged['marchas'] = $this->extractMarchValue($merged['application_secondary'] ?? []);
        }

        return $merged;
    }

    private function normalizeId(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = $this->normalizeText((string) $value);

        return ($str === '' || $str === '-') ? null : $str;
    }

    private function isTruthy(string $value): bool
    {
        return in_array(Str::lower($value), ['x', 'si', 'yes', 's', '1', 'true'], true);
    }

    private function generateUniqueSlug(string $base): string
    {
        $slug = Str::slug((string) $base) ?: 'product';
        $candidate = $slug;
        $counter = 2;

        while (Product::where('slug', $candidate)->exists()) {
            $candidate = $slug . '-' . $counter++;
        }

        return $candidate;
    }
}
