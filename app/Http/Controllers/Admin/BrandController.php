<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::ordered()->get();
        return Inertia::render('Admin/Brands/Index', compact('brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'logo'      => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        Brand::create([
            'name'      => ['es' => $data['name'], 'en' => $data['name'], 'pt' => $data['name']],
            'slug'      => Str::slug($data['name']),
            'logo'      => $logoPath,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Marca creada correctamente.');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:100',
            'logo'      => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $logoPath = $brand->logo;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('brands', 'public');
        }

        $brand->update([
            'name'      => ['es' => $data['name'], 'en' => $data['name'], 'pt' => $data['name']],
            'slug'      => Str::slug($data['name']),
            'logo'      => $logoPath,
            'is_active' => $data['is_active'] ?? $brand->is_active,
        ]);

        return back()->with('success', 'Marca actualizada correctamente.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Marca eliminada.');
    }
}
