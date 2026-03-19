<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Gearbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GearboxController extends Controller
{
    public function index()
    {
        $gearboxes = Gearbox::with('brand')->orderBy('order')->orderBy('name')->get()
            ->map(fn ($g) => [
                'id'                 => $g->id,
                'brand_id'           => $g->brand_id,
                'brand_name'         => $g->brand ? (is_array($g->brand->name) ? ($g->brand->name['es'] ?? '') : $g->brand->name) : '',
                'name'               => $g->name,
                'slug'               => $g->slug,
                'compatible_vehicles'=> $g->compatible_vehicles,
                'image_url'          => $g->image ? asset('storage/' . $g->image) : null,
                'is_active'          => $g->is_active,
                'order'              => $g->order,
            ]);

        $brands = Brand::active()->orderBy('order')->get()
            ->map(fn ($b) => [
                'id'   => $b->id,
                'name' => is_array($b->name) ? ($b->name['es'] ?? '') : $b->name,
            ]);

        return Inertia::render('Admin/Gearboxes/Index', compact('gearboxes', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'brand_id'            => 'required|exists:brands,id',
            'name'                => 'required|string|max:100',
            'compatible_vehicles' => 'nullable|string',
            'image'               => 'nullable|image|max:4096',
            'is_active'           => 'nullable|boolean',
            'order'               => 'nullable|string|max:20',
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
            'is_active'           => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'order'               => strtoupper(trim($data['order'] ?? 'A')),
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
            'is_active'           => 'nullable|boolean',
            'order'               => 'nullable|string|max:20',
        ]);

        $imagePath = $gearbox->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('gearboxes', 'public');
        }

        $gearbox->update([
            'brand_id'            => $data['brand_id'],
            'name'                => $data['name'],
            'slug'                => Str::slug($data['name']),
            'compatible_vehicles' => $data['compatible_vehicles'] ?? null,
            'image'               => $imagePath,
            'is_active'           => isset($data['is_active']) ? (bool) $data['is_active'] : $gearbox->is_active,
            'order'               => strtoupper(trim($data['order'] ?? $gearbox->order)),
        ]);

        return back()->with('success', 'Caja de cambios actualizada.');
    }

    public function destroy(Gearbox $gearbox)
    {
        if ($gearbox->image) {
            Storage::disk('public')->delete($gearbox->image);
        }
        $gearbox->delete();
        return back()->with('success', 'Caja de cambios eliminada.');
    }
}
