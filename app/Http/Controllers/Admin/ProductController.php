<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['gearbox.brand', 'primaryImage'])
            ->when($request->brand_id, fn ($q) => $q->whereHas('gearbox', fn ($q2) => $q2->where('brand_id', $request->brand_id)))
            ->when($request->gearbox_id, fn ($q) => $q->where('gearbox_id', $request->gearbox_id))
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('code_oem', 'like', "%{$request->search}%")
                   ->orWhere('cod_gs', 'like', "%{$request->search}%");
            }))
            ->orderBy('gearbox_id')
            ->orderBy('figure');

        $products  = $query->paginate(50)->withQueryString();
        $brands    = Brand::active()->ordered()->get();
        $gearboxes = $request->brand_id
            ? Gearbox::where('brand_id', $request->brand_id)->orderBy('name')->get()
            : collect();

        return Inertia::render('Admin/Products/Index', compact('products', 'brands', 'gearboxes'));
    }

    public function create()
    {
        $brands    = Brand::active()->ordered()->get();
        $gearboxes = Gearbox::orderBy('name')->get();

        return Inertia::render('Admin/Products/Create', compact('brands', 'gearboxes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'gearbox_id'              => 'required|exists:gearboxes,id',
            'figure'                  => 'nullable|string|max:20',
            'cod_gs'                  => 'nullable|string|max:50',
            'code_oem'                => 'required|string|max:100|unique:products,code_oem',
            'nombre'                  => 'nullable|string|max:255',
            'referencia_original'     => 'nullable|string|max:100',
            'referencias_adicionales' => 'nullable|string',
            'description'             => 'nullable|string',
            'nota_comercial'          => 'nullable|string|max:1000',
            'nota_interna'            => 'nullable|string|max:1000',
            'is_active'               => 'boolean',
            'versions'                => 'nullable|array',
            'images'                  => 'nullable|array',
            'images.*'                => 'image|max:4096',
        ]);

        $slug = $this->generateSlug($data['code_oem']);

        $product = Product::create([
            'gearbox_id'              => $data['gearbox_id'],
            'figure'                  => $data['figure'] ?? null,
            'cod_gs'                  => $data['cod_gs'] ?? null,
            'code_oem'                => $data['code_oem'],
            'slug'                    => $slug,
            'nombre'                  => ['es' => $data['nombre'] ?? null, 'en' => null, 'pt' => null],
            'referencia_original'     => $data['referencia_original'] ?? null,
            'referencias_adicionales' => $data['referencias_adicionales']
                ? array_map('trim', explode(';', $data['referencias_adicionales']))
                : null,
            'description'             => ['es' => $data['description'] ?? null, 'en' => null, 'pt' => null],
            'nota_comercial'          => $data['nota_comercial'] ?? null,
            'nota_interna'            => $data['nota_interna'] ?? null,
            'is_active'               => $data['is_active'] ?? true,
        ]);

        // Versiones (G60, G85...)
        if (!empty($data['versions'])) {
            foreach ($data['versions'] as $version) {
                $product->versions()->create(['version' => $version, 'is_available' => true]);
            }
        }

        // Imágenes
        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Producto creado.');
    }

    public function edit(Product $product)
    {
        $product->load(['versions', 'images', 'applications', 'documents', 'gearbox.brand']);
        $brands    = Brand::active()->ordered()->get();
        $gearboxes = Gearbox::orderBy('name')->get();

        return Inertia::render('Admin/Products/Edit', compact('product', 'brands', 'gearboxes'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'gearbox_id'              => 'required|exists:gearboxes,id',
            'figure'                  => 'nullable|string|max:20',
            'cod_gs'                  => 'nullable|string|max:50',
            'code_oem'                => "required|string|max:100|unique:products,code_oem,{$product->id}",
            'nombre'                  => 'nullable|string|max:255',
            'referencia_original'     => 'nullable|string|max:100',
            'referencias_adicionales' => 'nullable|string',
            'description'             => 'nullable|string',
            'nota_comercial'          => 'nullable|string|max:1000',
            'nota_interna'            => 'nullable|string|max:1000',
            'is_active'               => 'boolean',
            'versions'                => 'nullable|array',
        ]);

        // Regenerar slug si cambió el code_oem
        $slug = $product->slug;
        if ($product->code_oem !== $data['code_oem']) {
            $slug = $this->generateSlug($data['code_oem'], $product->id);
        }

        $product->update([
            'gearbox_id'              => $data['gearbox_id'],
            'figure'                  => $data['figure'] ?? null,
            'cod_gs'                  => $data['cod_gs'] ?? null,
            'code_oem'                => $data['code_oem'],
            'slug'                    => $slug,
            'nombre'                  => ['es' => $data['nombre'] ?? null, 'en' => $product->nombre['en'] ?? null, 'pt' => $product->nombre['pt'] ?? null],
            'referencia_original'     => $data['referencia_original'] ?? null,
            'referencias_adicionales' => $data['referencias_adicionales']
                ? array_map('trim', explode(';', $data['referencias_adicionales']))
                : null,
            'description'             => ['es' => $data['description'] ?? null, 'en' => $product->description['en'] ?? null, 'pt' => $product->description['pt'] ?? null],
            'nota_comercial'          => $data['nota_comercial'] ?? $product->nota_comercial,
            'nota_interna'            => $data['nota_interna'] ?? $product->nota_interna,
            'is_active'               => $data['is_active'] ?? $product->is_active,
        ]);

        // Sync versions
        if (isset($data['versions'])) {
            $product->versions()->delete();
            foreach ($data['versions'] as $version) {
                $product->versions()->create(['version' => $version, 'is_available' => true]);
            }
        }

        $this->storeImages($request, $product);

        return back()->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    // ── Imágenes masivas ────────────────────────────────────────────────

    /**
     * Asignar la misma imagen a múltiples productos (edición masiva).
     */
    public function bulkImage(Request $request)
    {
        $request->validate([
            'product_ids'   => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'image'         => 'required|image|max:4096',
        ]);

        $path = $request->file('image')->store('products', 'public');

        foreach ($request->product_ids as $productId) {
            ProductImage::create([
                'product_id' => $productId,
                'path'       => $path,
                'is_primary' => false,
                'sort_order' => 99,
            ]);
        }

        return back()->with('success', 'Imagen asignada a ' . count($request->product_ids) . ' productos.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function generateSlug(string $base, ?int $excludeId = null): string
    {
        $slug      = \Illuminate\Support\Str::slug($base);
        $candidate = $slug;
        $i         = 2;

        while (Product::where('slug', $candidate)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $candidate = $slug . '-' . $i++;
        }

        return $candidate;
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (!$request->hasFile('images')) return;

        $isPrimary = $product->images()->where('is_primary', true)->doesntExist();

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('products', 'public');
            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'is_primary' => $isPrimary && $index === 0,
                'sort_order' => $product->images()->max('sort_order') + $index + 1,
            ]);
        }
    }
}
