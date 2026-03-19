<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | /productos — Grid de marcas
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $brands = Brand::active()->ordered()->get();

        return view('web.products.index', compact('brands'));
    }

    /*
    |--------------------------------------------------------------------------
    | /productos/{brand} — Cajas de cambios de esa marca
    |--------------------------------------------------------------------------
    */

    public function brand(Brand $brand)
    {
        $gearboxes = Gearbox::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // $brands se sigue pasando para el buscador (hero-search)
        $brands = Brand::active()->ordered()->get();

        return view('web.products.brand', compact('brand', 'brands', 'gearboxes'));
    }

    /*
    |--------------------------------------------------------------------------
    | /productos/{brand}/{gearbox} — Productos de esa caja
    |--------------------------------------------------------------------------
    */

    public function gearbox(Brand $brand, Gearbox $gearbox)
    {
        abort_if($gearbox->brand_id !== $brand->id, 404);

        // Cajas hermanas de la misma marca → para el sidebar
        $gearboxes = Gearbox::where('brand_id', $brand->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Productos de la caja, con sus items cargados (eager)
        // Se cargan los items para los accesores de backward-compat (code_oem, etc.)
        $products = Product::where('gearbox_id', $gearbox->id)
            ->where('is_active', true)
            ->with(['primaryImage', 'items'])   // items: necesario para getCodeOemAttribute()
            ->orderBy('figure')
            ->orderBy('code_gs')
            ->get();

        // $brands sigue disponible para el buscador
        $brands = Brand::active()->ordered()->get();

        return view('web.products.gearbox', compact(
            'brand',
            'gearbox',
            'gearboxes',
            'brands',
            'products'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | /productos/{brand}/{gearbox}/{product} — Detalle del producto
    |--------------------------------------------------------------------------
    */

    public function show(Brand $brand, Gearbox $gearbox, Product $product)
    {
        abort_if($gearbox->brand_id !== $brand->id, 404);
        abort_if($product->gearbox_id !== $gearbox->id, 404);

        // Cargar relaciones necesarias para la vista
        $product->load([
            'images',
            'documents',
            'items',          // ← nueva relación principal
        ]);

        // Productos relacionados (misma caja, distinto producto)
        $related = Product::where('gearbox_id', $gearbox->id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->with(['primaryImage', 'items'])
            ->limit(4)
            ->get();

        return view('web.products.show', compact('brand', 'gearbox', 'product', 'related'));
    }

    /*
    |--------------------------------------------------------------------------
    | /productos/buscar — Búsqueda por marca / modelo / código
    |--------------------------------------------------------------------------
    */

    public function search(Request $request)
    {
        $brandSlug   = $request->input('marca');
        $gearboxSlug = $request->input('modelo');
        $code        = $request->input('codigo');

        // Marca + modelo → redirigir a la página de la caja
        if ($brandSlug && $gearboxSlug) {
            $brand   = Brand::where('slug', $brandSlug)->first();
            $gearbox = $brand
                ? Gearbox::where('brand_id', $brand->id)->where('slug', $gearboxSlug)->first()
                : null;

            if ($brand && $gearbox) {
                // Si se especificó un código, la redirección lleva a la caja
                // (la vista puede filtrar internamente si se implementa)
                return redirect()->route('products.gearbox', [$brand->slug, $gearbox->slug]);
            }
        }

        // Solo marca → redirigir a la página de la marca
        if ($brandSlug) {
            $brand = Brand::where('slug', $brandSlug)->first();
            if ($brand) {
                return redirect()->route('products.brand', $brand->slug);
            }
        }

        // Sin parámetros válidos → catálogo general
        return redirect()->route('products.index');
    }
}
