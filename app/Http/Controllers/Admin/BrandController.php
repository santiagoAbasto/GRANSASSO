<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('order')->get()->map(fn ($b) => [
            'id'           => $b->id,
            'name_es'      => is_array($b->name) ? ($b->name['es'] ?? '') : ($b->name ?? ''),
            'name_en'      => is_array($b->name) ? ($b->name['en'] ?? '') : '',
            'name_pt'      => is_array($b->name) ? ($b->name['pt'] ?? '') : '',
            'slug'         => $b->slug,
            'logo_url'     => $b->logo ? asset('storage/' . $b->logo) : null,
            'order'        => $b->order,
            'is_active'    => $b->is_active,
            'show_in_home' => $b->show_in_home ?? true,
        ]);

        return Inertia::render('Admin/Brands/Index', compact('brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_es'      => 'required|string|max:150',
            'name_en'      => 'nullable|string|max:150',
            'name_pt'      => 'nullable|string|max:150',
            'logo'         => 'nullable|image|max:2048',
            'order'        => 'nullable|string|max:20',
            'is_active'    => 'nullable|boolean',
            'show_in_home' => 'nullable|boolean',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        Brand::create([
            'name' => [
                'es' => $data['name_es'],
                'en' => $data['name_en'] ?: $data['name_es'],
                'pt' => $data['name_pt'] ?: $data['name_es'],
            ],
            'slug'         => Str::slug($data['name_es']),
            'logo'         => $logoPath,
            'order'        => strtoupper(trim($data['order'] ?? 'A')),
            'is_active'    => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'show_in_home' => isset($data['show_in_home']) ? (bool) $data['show_in_home'] : true,
        ]);

        return back()->with('success', 'Marca creada correctamente.');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name_es'      => 'required|string|max:150',
            'name_en'      => 'nullable|string|max:150',
            'name_pt'      => 'nullable|string|max:150',
            'logo'         => 'nullable|image|max:2048',
            'order'        => 'nullable|string|max:20',
            'is_active'    => 'nullable|boolean',
            'show_in_home' => 'nullable|boolean',
        ]);

        $logoPath = $brand->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        $brand->update([
            'name' => [
                'es' => $data['name_es'],
                'en' => $data['name_en'] ?: $data['name_es'],
                'pt' => $data['name_pt'] ?: $data['name_es'],
            ],
            'slug'         => Str::slug($data['name_es']),
            'logo'         => $logoPath,
            'order'        => strtoupper(trim($data['order'] ?? $brand->order)),
            'is_active'    => isset($data['is_active']) ? (bool) $data['is_active'] : $brand->is_active,
            'show_in_home' => isset($data['show_in_home']) ? (bool) $data['show_in_home'] : $brand->show_in_home,
        ]);

        return back()->with('success', 'Marca actualizada correctamente.');
    }

    public function toggleHome(Brand $brand)
    {
        $brand->update(['show_in_home' => ! $brand->show_in_home]);
        return back()->with('success', $brand->show_in_home ? 'Marca visible en Home.' : 'Marca oculta en Home.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        $brand->delete();
        return back()->with('success', 'Marca eliminada.');
    }
}
