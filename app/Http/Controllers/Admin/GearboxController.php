<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GearboxController extends Controller
{
    public function index()
    {
        $gearboxes = Gearbox::with('brand')->orderBy('name')->get();
        $brands    = Brand::active()->ordered()->get();

        return Inertia::render('Admin/Gearboxes/Index', compact('gearboxes', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_id'            => 'required|exists:brands,id',
            'name'                => 'required|string|max:100',
            'compatible_vehicles' => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'is_active'           => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('gearboxes', 'public');
        }

        Gearbox::create([
            'brand_id'            => $data['brand_id'],
            'name'                => $data['name'],
            'slug'                => Str::slug($data['name']),
            'compatible_vehicles' => $data['compatible_vehicles'] ?? null,
            'image'               => $imagePath,
            'is_active'           => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Caja de cambios creada.');
    }

    public function update(Request $request, Gearbox $gearbox)
    {
        $data = $request->validate([
            'brand_id'            => 'required|exists:brands,id',
            'name'                => 'required|string|max:100',
            'compatible_vehicles' => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'is_active'           => 'boolean',
        ]);

        $imagePath = $gearbox->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('gearboxes', 'public');
        }

        $gearbox->update([
            'brand_id'            => $data['brand_id'],
            'name'                => $data['name'],
            'slug'                => Str::slug($data['name']),
            'compatible_vehicles' => $data['compatible_vehicles'] ?? null,
            'image'               => $imagePath,
            'is_active'           => $data['is_active'] ?? $gearbox->is_active,
        ]);

        return back()->with('success', 'Caja de cambios actualizada.');
    }

    public function destroy(Gearbox $gearbox)
    {
        $gearbox->delete();
        return back()->with('success', 'Caja de cambios eliminada.');
    }
}
