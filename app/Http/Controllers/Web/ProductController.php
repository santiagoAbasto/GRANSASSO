<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    /**
     * /productos — grid de marcas
     */
    public function index()
    {
        $brands = Brand::active()->ordered()->get();

        $bannerSection = Page::where('slug', 'products')->first()
            ?->sections()->where('type', 'banner')->where('is_active', true)->with('images')->first();
        $bannerSlides = $bannerSection?->images->sortBy('order') ?? collect();

        return view('web.products.index', compact('brands', 'bannerSlides'));
    }

    /**
     * /productos/{brand} — cajas de cambios de esa marca
     */
    public function brand(Brand $brand)
    {
        $brands    = Brand::active()->ordered()->get();
        $gearboxes = Gearbox::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        $gearboxes->each(fn (Gearbox $gearbox) => $gearbox->setRelation('brand', $brand));

        return view('web.products.brand', compact('brand', 'brands', 'gearboxes'));
    }

    /**
     * /productos/{brand}/{gearbox} — productos de esa caja
     */
    public function gearbox($brandSlug, $gearboxSlug)
    {
        $brand = Brand::where('slug', $brandSlug)->firstOrFail();

        $gearbox = Gearbox::where('slug', $gearboxSlug)
            ->where('brand_id', $brand->id)
            ->firstOrFail();

        $brands = Brand::active()->ordered()->get();
        $gearbox->setRelation('brand', $brand);

        $products = Product::where('gearbox_id', $gearbox->id)
            ->where('is_active', true)
            ->with(['primaryImage', 'items'])
            ->orderBy('figure')
            ->get();

        $products->each(fn (Product $product) => $product->setRelation('gearbox', $gearbox));

        $this->decorateProductsForCards($products);

        return view('web.products.gearbox', compact('brand', 'gearbox', 'brands', 'products'));
    }

    /**
     * /productos/{brand}/{gearbox}/{product} — detalle del producto
     */
    public function show($brandSlug, $gearboxSlug, $productCode)
    {
        $brand = Brand::where('slug', $brandSlug)->firstOrFail();

        $gearbox = Gearbox::where('slug', $gearboxSlug)
            ->where('brand_id', $brand->id)
            ->firstOrFail();

        $product = Product::where(function ($q) use ($productCode) {
                $q->where('code_gs', $productCode)
                  ->orWhere('slug', $productCode);
            })
            ->where('gearbox_id', $gearbox->id)
            ->firstOrFail();

        $product->load(['images', 'items', 'primaryImage']);
        $gearbox->setRelation('brand', $brand);
        $product->setRelation('gearbox', $gearbox);

        $related = Product::where('gearbox_id', $gearbox->id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['primaryImage', 'items'])
            ->limit(4)
            ->get();

        $related->each(fn (Product $rel) => $rel->setRelation('gearbox', $gearbox));

        $productView = $this->buildProductViewData($product);
        $this->decorateProductsForCards($related);

        return view('web.products.show', compact('brand', 'gearbox', 'product', 'related', 'productView'));
    }

    /**
     * /productos/{brand}/{gearbox}/{product}/pdf — ficha técnica en PDF
     */
    public function pdf($brandSlug, $gearboxSlug, $productCode)
    {
        $locale = app()->getLocale();

        $brand = Brand::where('slug', $brandSlug)->firstOrFail();

        $gearbox = Gearbox::where('slug', $gearboxSlug)
            ->where('brand_id', $brand->id)
            ->firstOrFail();

        $product = Product::where(function ($q) use ($productCode) {
                $q->where('code_gs', $productCode)
                  ->orWhere('slug', $productCode);
            })
            ->where('gearbox_id', $gearbox->id)
            ->firstOrFail();

        $product->load(['images', 'items', 'primaryImage']);

        $productView      = $this->buildProductViewData($product);
        $oemCode          = $productView['primary_oem_code'];
        $productName      = $productView['display_name'];
        $productDesc      = $productView['display_description'];
        $refOriginal      = $productView['ref_original'];
        $refsAdicionales  = collect($productView['refs_adicionales']);
        $altOemCodes      = collect($productView['alt_oem_codes']);
        $applications     = collect($productView['applications']);
        $variants         = collect($productView['variant_labels']);
        $catalogRows      = collect($productView['catalog_rows']);
        $catalogVariants  = collect($productView['catalog_variant_columns']);
        $mainImage        = $product->primaryImage ?? $product->images->first();
        $allImages        = $product->images;
        $codeGs           = $productView['code_gs'];
        $figure           = $productView['figure'];

        $pdf = Pdf::loadView('web.products.pdf', compact(
            'brand', 'gearbox', 'product', 'oemCode', 'productName', 'productDesc',
            'refOriginal', 'refsAdicionales', 'altOemCodes', 'applications', 'variants',
            'mainImage', 'allImages', 'codeGs', 'figure', 'locale', 'catalogRows', 'catalogVariants'
        ))->setPaper('a4');

        $prefix = __('ui.product_pdf.file_name_prefix');

        return $pdf->stream("{$prefix}-{$oemCode}.pdf");
    }

    /**
     * Buscar por marca/modelo/código (código GS o OEM).
     * Sin código → redirige a la página de marca/modelo correspondiente.
     * Con código → busca en products.code_gs y product_items.oem_code.
     */
    public function search(Request $request)
    {
        $brandSlug   = trim($request->input('marca', ''));
        $gearboxSlug = trim($request->input('modelo', ''));
        $code        = trim($request->input('codigo', ''));

        // Sin código: redirigir según marca/modelo seleccionados
        if ($code === '') {
            if ($brandSlug && $gearboxSlug) {
                $brand   = Brand::where('slug', $brandSlug)->first();
                $gearbox = $brand ? Gearbox::where('brand_id', $brand->id)->where('slug', $gearboxSlug)->first() : null;
                if ($brand && $gearbox) {
                    return redirect()->route('products.gearbox', [$brand->slug, $gearbox->slug]);
                }
            }
            if ($brandSlug) {
                $brand = Brand::where('slug', $brandSlug)->first();
                if ($brand) return redirect()->route('products.brand', $brand->slug);
            }
            return redirect()->route('products.index');
        }

        // Con código: buscar por code_gs (productos) o oem_code (items)
        $query = Product::query()
            ->where('is_active', true)
            ->with(['gearbox.brand', 'primaryImage', 'items']);

        // Filtrar por marca si se seleccionó
        $brand   = $brandSlug ? Brand::where('slug', $brandSlug)->first() : null;
        $gearbox = null;
        if ($brand) {
            $gearbox = $gearboxSlug
                ? Gearbox::where('brand_id', $brand->id)->where('slug', $gearboxSlug)->first()
                : null;
            if ($gearbox) {
                $query->where('gearbox_id', $gearbox->id);
            } else {
                $gearboxIds = Gearbox::where('brand_id', $brand->id)->pluck('id');
                $query->whereIn('gearbox_id', $gearboxIds);
            }
        }

        // Buscar por code_gs o por oem_code en product_items
        $query->where(function ($q) use ($code) {
            $q->where('code_gs', 'like', '%' . $code . '%')
              ->orWhereHas('items', fn ($qi) => $qi->where('oem_code', 'like', '%' . $code . '%'));
        });

        $products = $query->orderBy('code_gs')->get();

        $this->decorateProductsForCards($products);

        // Si hay exactamente un resultado, redirigir directo al producto
        if ($products->count() === 1) {
            $p = $products->first();
            $g = $p->gearbox;
            $b = $g?->brand;
            if ($g && $b) {
                return redirect()->route('products.show', [
                    $b->slug,
                    $g->slug,
                    $p->code_gs ?? $p->slug,
                ]);
            }
        }

        $brands = Brand::active()->ordered()->get();

        return view('web.products.search', compact('products', 'code', 'brands', 'brandSlug', 'gearboxSlug'));
    }

    private function decorateProductsForCards(iterable $products): void
    {
        foreach ($products as $product) {
            if ($product instanceof Product) {
                $product->setAttribute('catalog_card', $this->buildProductCardData($product));
            }
        }
    }

    private function buildProductViewData(Product $product): array
    {
        $items = $this->getSortedItems($product);
        $card  = $this->buildProductCardData($product, $items);
        $gearboxCompatibleVehicles = $this->normalizeCatalogText($product->gearbox?->compatible_vehicles);

        $refOriginal = $items
            ->map(fn (ProductItem $item) => $item->getReferenciaOriginal())
            ->filter()
            ->first();

        $refsAdicionales = $items
            ->flatMap(fn (ProductItem $item) => $item->getReferenciasAdicionales())
            ->filter()
            ->unique()
            ->values()
            ->all();

        $altOemCodes = $items
            ->pluck('oem_code')
            ->filter()
            ->unique()
            ->filter(fn (string $oemCode) => $oemCode !== $card['primary_oem_code'])
            ->values()
            ->all();

        $notes = $items
            ->pluck('notes')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $applications = $items
            ->map(fn (ProductItem $item) => $this->extractVehicleApplication($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($gearboxCompatibleVehicles) {
            $applications = array_values(array_filter(
                $applications,
                fn (string $application) => $this->normalizeCatalogText($application) !== $gearboxCompatibleVehicles
            ));
        }

        $catalogRows = $this->buildCatalogRows($product, $items, $gearboxCompatibleVehicles);

        $catalogVariantColumns = $this->sortLabels(
            collect($catalogRows)
                ->flatMap(fn (array $row) => $row['variants'])
                ->filter()
                ->unique()
                ->values()
                ->all()
        );

        return [
            'card'                    => $card,
            'primary_oem_code'        => $card['primary_oem_code'],
            'display_name'            => $product->getName(),
            'display_description'     => $product->getDescription(),
            'figure'                  => $card['figure'],
            'code_gs'                 => $product->code_gs,
            'variant_labels'          => $card['variant_labels'],
            'ref_original'            => $refOriginal,
            'refs_adicionales'        => $refsAdicionales,
            'alt_oem_codes'           => $altOemCodes,
            'applications'            => $applications,
            'notes'                   => $notes,
            'catalog_rows'            => $catalogRows,
            'catalog_variant_columns' => $catalogVariantColumns,
        ];
    }

    private function buildProductCardData(Product $product, ?Collection $items = null): array
    {
        $items ??= $this->getSortedItems($product);

        $primaryOemCode = $items->pluck('oem_code')->filter()->first() ?? $product->code_gs ?? '—';
        $figure         = $product->figure ?? $items->pluck('figure')->filter()->first();
        $description    = $product->getName() ?? $product->getDescription();
        $variantLabels  = $this->collectProductVariantLabels($items)->values()->all();

        return [
            'title_code'       => $product->code_gs ?: $primaryOemCode,
            'primary_oem_code' => $primaryOemCode,
            'code_gs'          => $product->code_gs,
            'figure'           => $figure,
            'description'      => $description,
            'variant_labels'   => $variantLabels,
        ];
    }

    private function buildCatalogRows(Product $product, Collection $items, ?string $fallbackApplication = null): array
    {
        if ($items->isEmpty()) {
            return [[
                'figure'                => $product->figure,
                'code_gs'               => $product->code_gs,
                'oem_code'              => null,
                'description'           => $product->getDescription() ?? $product->getName(),
                'variants'              => [],
                'applications'          => $fallbackApplication ? [$fallbackApplication] : [],
                'reference_original'    => null,
                'additional_references' => [],
                'notes'                 => null,
            ]];
        }

        $rows = [];

        foreach ($items as $item) {
            $applications = collect([$this->extractVehicleApplication($item)])
                ->filter()
                ->values()
                ->all();

            if ($applications === [] && $fallbackApplication) {
                $applications = [$fallbackApplication];
            }

            $additionalReferences = collect($item->getReferenciasAdicionales())
                ->filter()
                ->unique()
                ->values()
                ->all();

            $groupKey = json_encode([
                $item->figure ?? $product->figure,
                $item->oem_code,
                $applications,
                $item->getReferenciaOriginal(),
                $additionalReferences,
                $item->notes,
            ], JSON_UNESCAPED_UNICODE);

            if (! isset($rows[$groupKey])) {
                $rows[$groupKey] = [
                    'figure'                => $item->figure ?? $product->figure,
                    'code_gs'               => $product->code_gs,
                    'oem_code'              => $item->oem_code,
                    'description'           => $item->extra_data['catalog_row']['description']
                        ?? $product->getDescription()
                        ?? $product->getName(),
                    'variants'              => [],
                    'applications'          => $applications,
                    'reference_original'    => $item->getReferenciaOriginal(),
                    'additional_references' => $additionalReferences,
                    'notes'                 => $item->notes,
                ];
            }

            $rows[$groupKey]['variants'] = $this->sortLabels(array_merge(
                $rows[$groupKey]['variants'],
                $this->extractItemVariantLabels($item)
            ));

            $rows[$groupKey]['applications'] = array_values(array_unique(array_merge(
                $rows[$groupKey]['applications'],
                $applications
            )));
        }

        return array_values($rows);
    }

    private function collectProductVariantLabels(Collection $items): Collection
    {
        return collect($this->sortLabels(
            $items
                ->flatMap(fn (ProductItem $item) => $this->extractItemVariantLabels($item))
                ->filter()
                ->unique()
                ->values()
                ->all()
        ));
    }

    private function extractItemVariantLabels(ProductItem $item): array
    {
        $variants = $this->extractExtraDataVariantLabels($item->extra_data ?? []);

        if ($variants !== []) {
            return $variants;
        }

        $application = trim((string) ($item->application ?? ''));

        if ($this->looksLikeVariantLabel($application)) {
            return [$this->normalizeVariantLabel($application)];
        }

        return [];
    }

    private function extractVehicleApplication(ProductItem $item): ?string
    {
        $application = preg_replace('/\s+/', ' ', trim((string) ($item->application ?? '')));

        if (! $application) {
            return null;
        }

        return $this->looksLikeVariantLabel($application) ? null : $application;
    }

    private function normalizeCatalogText(?string $value): ?string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $value));

        return $normalized !== '' ? $normalized : null;
    }

    private function extractExtraDataVariantLabels(array $extraData): array
    {
        $variants = [];

        foreach ($extraData as $key => $value) {
            if (in_array($key, ['referencia_original', 'referencias_adicionales'], true)) {
                continue;
            }

            if ($value === true && $this->looksLikeVariantLabel((string) $key)) {
                $variants[] = $this->normalizeVariantLabel((string) $key);
            }
        }

        return $this->sortLabels($variants);
    }

    private function looksLikeVariantLabel(?string $value): bool
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));

        if ($value === '' || mb_strlen($value) > 12) {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z]{1,4}\s?\d{1,4}(?:[\/-]\d{1,4})?$/', $value);
    }

    private function normalizeVariantLabel(string $value): string
    {
        return strtoupper(preg_replace('/\s+/', ' ', trim($value)));
    }

    private function sortLabels(array $labels): array
    {
        $labels = array_values(array_unique(array_filter($labels)));

        usort($labels, fn (string $left, string $right) => strnatcasecmp($left, $right));

        return $labels;
    }

    private function getSortedItems(Product $product): Collection
    {
        $items = $product->relationLoaded('items')
            ? $product->items
            : $product->items()->get();

        return $items
            ->sort(fn (ProductItem $left, ProductItem $right) => strnatcasecmp(
                implode('|', [$left->figure ?? 'zzz', $left->oem_code ?? 'zzz', $left->application ?? 'zzz']),
                implode('|', [$right->figure ?? 'zzz', $right->oem_code ?? 'zzz', $right->application ?? 'zzz'])
            ))
            ->values();
    }
}
