<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * /productos — grid de marcas
     */
    public function index()
    {
        $brands = Brand::active()->ordered()->get();

        return view('web.products.index', compact('brands'));
    }

    /**
     * /productos/{brand} — cajas de cambios de esa marca
     */
    public function brand(Brand $brand)
    {
        $brands    = Brand::active()->ordered()->get();
        $gearboxes = Gearbox::where('brand_id', $brand->id)
            ->where('is_active', true)
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
            ->with('primaryImage')
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

        $product->load(['images', 'versions', 'applications', 'documents']);

        $related = Product::where('gearbox_id', $gearbox->id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with('primaryImage')
            ->limit(4)
            ->get();

        return view('web.products.show', compact('brand', 'gearbox', 'product', 'related'));
    }

    /**
     * Buscar por marca/modelo/código (usado en el hero y la página de productos)
     */
    public function search(Request $request)
    {
        $brandSlug   = $request->input('marca');
        $gearboxSlug = $request->input('modelo');
        $code        = $request->input('codigo');

        // Si hay marca y modelo → redirigir a gearbox o filtrar
        if ($brandSlug && $gearboxSlug) {
            $brand   = Brand::where('slug', $brandSlug)->first();
            $gearbox = $brand ? Gearbox::where('brand_id', $brand->id)->where('slug', $gearboxSlug)->first() : null;

            if ($brand && $gearbox) {
                if ($code) {
                    return redirect()->route('products.gearbox', [$brand->slug, $gearbox->slug])
                        ->withInput();
                }
                return redirect()->route('products.gearbox', [$brand->slug, $gearbox->slug]);
            }
        }

        if ($brandSlug) {
            $brand = Brand::where('slug', $brandSlug)->first();
            if ($brand) {
                return redirect()->route('products.brand', $brand->slug);
            }
        }

        return redirect()->route('products.index');
    }
}
