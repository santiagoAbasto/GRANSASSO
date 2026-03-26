<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Page;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

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

        return view('web.products.brand', compact('brand', 'brands', 'gearboxes'));
    }

    /**
     * /productos/{brand}/{gearbox} — productos de esa caja
     */
    public function gearbox(Brand $brand, Gearbox $gearbox)
    {
        abort_if($gearbox->brand_id !== $brand->id, 404);

        $brands   = Brand::active()->ordered()->get();
        $products = Product::where('gearbox_id', $gearbox->id)
            ->where('is_active', true)
            ->with(['primaryImage', 'items'])
            ->orderBy('figure')
            ->get();

        return view('web.products.gearbox', compact('brand', 'gearbox', 'brands', 'products'));
    }

    /**
     * /productos/{brand}/{gearbox}/{product} — detalle del producto
     */
    public function show(Brand $brand, Gearbox $gearbox, Product $product)
    {
        abort_if($gearbox->brand_id !== $brand->id, 404);
        abort_if($product->gearbox_id !== $gearbox->id, 404);

        $product->load(['images', 'items']);

        $related = Product::where('gearbox_id', $gearbox->id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['primaryImage', 'items'])
            ->limit(4)
            ->get();

        return view('web.products.show', compact('brand', 'gearbox', 'product', 'related'));
    }

    /**
     * /productos/{brand}/{gearbox}/{product}/pdf — ficha técnica en PDF
     */
    public function pdf(Brand $brand, Gearbox $gearbox, Product $product)
    {
        abort_if($gearbox->brand_id !== $brand->id, 404);
        abort_if($product->gearbox_id !== $gearbox->id, 404);

        $product->load(['images', 'items']);

        $primaryItem     = $product->items->first();
        $oemCode         = $primaryItem?->oem_code ?? $product->code_gs ?? '—';
        $productName     = $product->getName();
        $productDesc     = $product->getDescription();
        $refOriginal     = $product->items->map(fn ($i) => $i->getReferenciaOriginal())->filter()->first();
        $refsAdicionales = $product->items->flatMap(fn ($i) => $i->getReferenciasAdicionales())->filter()->unique()->values();
        $variants        = collect($product->getAvailableVariants())->map(fn ($v) => strtoupper($v));
        $variantSet      = $variants->map(fn ($v) => strtolower($v))->toArray();
        $applications    = $product->items->filter(fn ($i) => $i->application !== null)->pluck('application')->unique()->filter(fn ($a) => !in_array(strtolower($a), $variantSet, true))->values();
        $altOemCodes     = $product->items->pluck('oem_code')->filter()->unique()->filter(fn ($o) => $o !== $oemCode)->values();
        $mainImage       = $product->primaryImage ?? $product->images->first();
        $allImages       = $product->images;
        $codeGs          = $product->code_gs;
        $figure          = $product->figure;

        $pdf = Pdf::loadView('web.products.pdf', compact(
            'brand', 'gearbox', 'product', 'oemCode', 'productName', 'productDesc',
            'refOriginal', 'refsAdicionales', 'altOemCodes', 'applications', 'variants',
            'mainImage', 'allImages', 'codeGs', 'figure'
        ))->setPaper('a4');

        return $pdf->stream("ficha-{$oemCode}.pdf");
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
        $brand   = $brandSlug   ? Brand::where('slug', $brandSlug)->first()   : null;
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

        // Si hay exactamente un resultado, redirigir directo al producto
        if ($products->count() === 1) {
            $p = $products->first();
            $g = $p->gearbox;
            $b = $g?->brand;
            if ($g && $b) {
                return redirect()->route('products.show', [$b->slug, $g->slug, $p->slug]);
            }
        }

        $brands = Brand::active()->ordered()->get();

        return view('web.products.search', compact('products', 'code', 'brands', 'brandSlug', 'gearboxSlug'));
    }
}
