<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Novedad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NovedadAdminController extends Controller
{
    public function index()
    {
        $novedades = Novedad::orderByDesc('created_at')->get();

        return Inertia::render('Admin/PrivateZone/Novedades', [
            'novedades' => $novedades,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'category'  => ['nullable', 'string', 'max:100'],
            'excerpt'   => ['nullable', 'string'],
            'body'      => ['nullable', 'string'],
            'image'     => ['nullable', 'image', 'max:3072'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('novedades', 'public');
        }

        Novedad::create($data);

        return back()->with('success', 'Novedad creada.');
    }

    public function update(Request $request, Novedad $novedad)
    {
        $data = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'category'  => ['nullable', 'string', 'max:100'],
            'excerpt'   => ['nullable', 'string'],
            'body'      => ['nullable', 'string'],
            'image'     => ['nullable', 'image', 'max:3072'],
            'is_active' => ['boolean'],
        ]);

        if ($request->hasFile('image')) {
            if ($novedad->image) {
                Storage::disk('public')->delete($novedad->image);
            }
            $data['image'] = $request->file('image')->store('novedades', 'public');
        }

        $novedad->update($data);

        return back()->with('success', 'Novedad actualizada.');
    }

    public function destroy(Novedad $novedad)
    {
        if ($novedad->image) {
            Storage::disk('public')->delete($novedad->image);
        }
        $novedad->delete();

        return back()->with('success', 'Novedad eliminada.');
    }
}
