<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProductController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX — listado con filtros
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = Product::with(['gearbox.brand', 'primaryImage', 'items'])
            ->when(
                $request->brand_id,
                fn ($q) => $q->whereHas('gearbox', fn ($q2) => $q2->where('brand_id', $request->brand_id))
            )
            ->when($request->gearbox_id, fn ($q) => $q->where('gearbox_id', $request->gearbox_id))
            // Búsqueda libre: descripción, cod GS o figura
            ->when($request->search, fn ($q) => $q->where(function ($q2) use ($request) {
                $term = "%{$request->search}%";
                $q2->where('code_gs', 'like', $term)
                   ->orWhere('figure', 'like', $term)
                   ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.es')) LIKE ?", [$term]);
            }))
            // OEM exacto: match exacto en cualquiera de los items
            ->when($request->oem_exact, fn ($q) => $q->whereHas(
                'items', fn ($q2) => $q2->where('oem_code', trim($request->oem_exact))
            ))
            // Cod GS: match parcial en el campo code_gs del producto
            ->when($request->cod_gs, fn ($q) => $q->where('code_gs', 'like', "%{$request->cod_gs}%"))
            ->orderBy('gearbox_id')
            ->orderBy('figure');

        $products = $query->paginate(50)->withQueryString()
            ->through(fn ($p) => $this->mapProductForIndex($p));

        $brands = Brand::active()->ordered()->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name]);

        $gearboxes = $request->brand_id
            ? Gearbox::where('brand_id', $request->brand_id)->orderBy('name')->get()
                ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'brand_id' => $g->brand_id])
            : collect();

        return Inertia::render('Admin/Products/Index', compact('products', 'brands', 'gearboxes'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE / STORE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $brands    = Brand::active()->ordered()->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name]);
        $gearboxes = Gearbox::orderBy('name')->get()
            ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'brand_id' => $g->brand_id]);

        return Inertia::render('Admin/Products/Create', compact('brands', 'gearboxes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // ── Producto base ─────────────────────────────────────────────
            'gearbox_id'              => 'required|exists:gearboxes,id',
            'figure'                  => 'nullable|string|max:100',
            'code_gs'                 => 'nullable|string|max:100',
            'name_es'                 => 'nullable|string|max:255',
            'description_es'          => 'nullable|string',
            'is_active'               => 'boolean',
            // ── Item principal ────────────────────────────────────────────
            'code_oem'                => 'nullable|string|max:100',
            'referencia_original'     => 'nullable|string|max:200',
            'referencias_adicionales' => 'nullable|string',
            'notes'                   => 'nullable|string|max:1000',
            'application'             => 'nullable|string|max:500',
            'application_type'        => 'nullable|in:generic,specific',
            'variants'                => 'nullable|array',
            'variants.*'              => 'string',
            // ── Imágenes ──────────────────────────────────────────────────
            'images'                  => 'nullable|array',
            'images.*'                => 'image|max:4096',
        ]);

        $slugBase = $data['code_gs'] ?? $data['code_oem'] ?? $data['figure'] ?? 'product';

        $product = Product::create([
            'gearbox_id'  => $data['gearbox_id'],
            'figure'      => $data['figure'] ?? null,
            'code_gs'     => $data['code_gs'] ?? null,
            'name'        => ['es' => $data['name_es'] ?? null, 'en' => null, 'pt' => null],
            'description' => ['es' => $data['description_es'] ?? null, 'en' => null, 'pt' => null],
            'slug'        => $this->generateSlug($slugBase),
            'is_active'   => $data['is_active'] ?? true,
        ]);

        // Crear 1 item por variante (o 1 item genérico si no hay variantes)
        $this->syncItems($product, $data);

        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Producto creado.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT / UPDATE
    |--------------------------------------------------------------------------
    */

    public function edit(Product $product)
    {
        $product->load(['items', 'images', 'gearbox.brand']);

        $brands    = Brand::active()->ordered()->get()
            ->map(fn ($b) => ['id' => $b->id, 'name' => $b->name]);
        $gearboxes = Gearbox::orderBy('name')->get()
            ->map(fn ($g) => ['id' => $g->id, 'name' => $g->name, 'brand_id' => $g->brand_id]);

        return Inertia::render('Admin/Products/Edit', [
            'product'   => $this->mapProductForEdit($product),
            'brands'    => $brands,
            'gearboxes' => $gearboxes,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            // ── Producto base ─────────────────────────────────────────────
            'gearbox_id'              => 'required|exists:gearboxes,id',
            'figure'                  => 'nullable|string|max:100',
            'code_gs'                 => 'nullable|string|max:100',
            'name_es'                 => 'nullable|string|max:255',
            'description_es'          => 'nullable|string',
            'is_active'               => 'boolean',
            // ── Item principal ────────────────────────────────────────────
            'code_oem'                => 'nullable|string|max:100',
            'referencia_original'     => 'nullable|string|max:200',
            'referencias_adicionales' => 'nullable|string',
            'notes'                   => 'nullable|string|max:1000',
            'application'             => 'nullable|string|max:500',
            'application_type'        => 'nullable|in:generic,specific',
            'variants'                => 'nullable|array',
            'variants.*'              => 'string',
            // ── Imágenes ──────────────────────────────────────────────────
            'images'                  => 'nullable|array',
            'images.*'                => 'image|max:4096',
        ]);

        // ── Actualizar producto base ───────────────────────────────────────
        $product->update([
            'gearbox_id'  => $data['gearbox_id'],
            'figure'      => $data['figure'] ?? null,
            'code_gs'     => $data['code_gs'] ?? null,
            'name'        => [
                'es' => $data['name_es'] ?? null,
                'en' => $product->name['en'] ?? null,
                'pt' => $product->name['pt'] ?? null,
            ],
            'description' => [
                'es' => $data['description_es'] ?? null,
                'en' => $product->description['en'] ?? null,
                'pt' => $product->description['pt'] ?? null,
            ],
            'is_active'   => $data['is_active'] ?? $product->is_active,
        ]);

        // Sincronizar items: elimina y recrea según variantes del formulario
        $this->syncItems($product, $data);

        $this->storeImages($request, $product);

        return back()->with('success', 'Producto actualizado.');
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Producto eliminado.');
    }

    /*
    |--------------------------------------------------------------------------
    | BULK IMAGE
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | MAPPERS — transforman modelos en arrays para el frontend
    |--------------------------------------------------------------------------
    */

    private function mapProductForIndex(Product $p): array
    {
        // Todos los OEMs únicos de los items
        $oemCodes = $p->items
            ->pluck('oem_code')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Variantes únicas (application de cada item)
        $variants = $p->items
            ->pluck('application')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return [
            'id'            => $p->id,
            'figure'        => $p->figure,
            'code_gs'       => $p->code_gs,
            'oem_codes'     => $oemCodes,
            'name_es'       => $p->getName(),
            'gearbox'       => [
                'id'                  => $p->gearbox->id,
                'name'                => $p->gearbox->name,
                'compatible_vehicles' => $p->gearbox->compatible_vehicles,
                'brand'               => ['name' => $p->gearbox->brand->name ?? ''],
            ],
            'variants'      => $variants,
            'items_count'   => $p->items->count(),
            'primary_image' => $p->primaryImage
                ? ['url' => asset('storage/' . $p->primaryImage->path)]
                : null,
            'is_active'     => $p->is_active,
        ];
    }

    private function mapProductForEdit(Product $p): array
    {
        $primaryItem = $p->items->first();

        // Variantes: applications de TODOS los items del producto
        // (con la nueva arquitectura, cada variante es un item separado)
        $variants = $p->items
            ->pluck('application')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // OEM del item principal (todos los items del mismo producto
        // generalmente comparten el mismo OEM)
        $primaryOem = $primaryItem?->oem_code;

        // Referencias del item principal
        $refOriginal  = $primaryItem?->getReferenciaOriginal();
        $refsAdicionales = $primaryItem
            ? implode('; ', $primaryItem->getReferenciasAdicionales())
            : '';

        return [
            // ── Producto base ──────────────────────────────────────────────
            'id'              => $p->id,
            'gearbox_id'      => $p->gearbox_id,
            'figure'          => $p->figure,
            'code_gs'         => $p->code_gs,
            'name_es'         => $p->name['es'] ?? null,
            'description_es'  => $p->description['es'] ?? null,
            'is_active'       => $p->is_active,
            'gearbox'         => ['brand_id' => $p->gearbox->brand_id],
            // ── Item principal (editable) ──────────────────────────────────
            'code_oem'               => $primaryOem,
            'referencia_original'    => $refOriginal,
            'referencias_adicionales' => $refsAdicionales,
            'notes'                  => $primaryItem?->notes,
            // ── Variantes: vienen del campo application de cada item ───────
            'variants'               => $variants,
            // ── Todos los items (solo lectura si hay múltiples) ────────────
            'items'          => $p->items->map(fn ($i) => [
                'id'               => $i->id,
                'oem_code'         => $i->oem_code,
                'figure'           => $i->figure,
                'application'      => $i->application,
                'application_type' => $i->application_type,
                'notes'            => $i->notes,
                'variants'         => $i->getCompatibleVariants(), // ["G60"] o []
            ])->values(),
            // ── Imágenes ──────────────────────────────────────────────────
            'images'         => $p->images->map(fn ($img) => [
                'id'   => $img->id,
                'url'  => asset('storage/' . $img->path),
                'path' => $img->path,
            ])->values(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Sincroniza los ProductItems del producto con los datos del formulario.
     *
     * Nueva arquitectura: cada variante = 1 item separado con application=variante.
     * Si no hay variantes → 1 item genérico con application=null.
     *
     * El método:
     *   1. Elimina los items del OEM principal (respeta otros OEMs si existen)
     *   2. Crea nuevos items según variantes seleccionadas
     *
     * El OEM del formulario aplica a TODOS los items creados.
     * Las referencias van en extra_data del primer item.
     */
    private function syncItems(Product $product, array $data): void
    {
        $oem      = ! empty($data['code_oem']) ? trim($data['code_oem']) : null;
        $figure   = ! empty($data['figure'])   ? trim($data['figure'])   : null;
        $variants = $data['variants'] ?? [];
        $notes    = ! empty($data['notes']) ? trim($data['notes']) : null;

        // Construir extra_data con referencias
        $extraData = $this->buildRefExtraData(
            $data['referencia_original']     ?? null,
            $data['referencias_adicionales'] ?? null
        );

        // Eliminar items existentes con este OEM (limpiar antes de recrear)
        // Si el producto tiene otros OEMs (de importaciones previas), se conservan
        if ($oem !== null) {
            $product->items()->where('oem_code', $oem)->delete();
        } else {
            // Sin OEM: eliminar solo items genéricos sin OEM
            $product->items()->whereNull('oem_code')->delete();
        }

        if (empty($variants)) {
            // Sin variantes → 1 item genérico
            ProductItem::create([
                'product_id'       => $product->id,
                'figure'           => $figure,
                'oem_code'         => $oem,
                'application'      => null,
                'application_type' => 'generic',
                'notes'            => $notes,
                'extra_data'       => $extraData ?: null,
            ]);
        } else {
            // 1 item por variante
            foreach ($variants as $i => $variant) {
                $variant = trim($variant);
                if ($variant === '') {
                    continue;
                }

                ProductItem::create([
                    'product_id'       => $product->id,
                    'figure'           => $figure,
                    'oem_code'         => $oem,
                    'application'      => $variant,
                    'application_type' => 'specific',
                    'notes'            => $notes,
                    // Referencias solo en el primer item para no duplicar
                    'extra_data'       => ($i === 0 && ! empty($extraData)) ? $extraData : null,
                ]);
            }
        }
    }

    /**
     * Construye extra_data con las referencias del formulario.
     * Las variantes ya NO van en extra_data — viven en el campo application.
     */
    private function buildRefExtraData(?string $refOriginal, ?string $refsAdicionales): array
    {
        $extra = [];

        if ($refOriginal !== null && trim($refOriginal) !== '') {
            $extra['referencia_original'] = trim($refOriginal);
        }

        if ($refsAdicionales !== null && trim($refsAdicionales) !== '') {
            $refs = array_values(array_filter(
                array_map('trim', explode(';', $refsAdicionales))
            ));
            if (! empty($refs)) {
                $extra['referencias_adicionales'] = $refs;
            }
        }

        return $extra;
    }

    private function generateSlug(string $base, ?int $excludeId = null): string
    {
        $slug      = Str::slug($base) ?: 'product';
        $candidate = $slug;
        $counter   = 2;

        while (
            Product::where('slug', $candidate)
                   ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                   ->exists()
        ) {
            $candidate = $slug . '-' . $counter++;
        }

        return $candidate;
    }

    private function storeImages(Request $request, Product $product): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

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
