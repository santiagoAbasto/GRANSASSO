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
        $novedades = Novedad::orderByDesc('created_at')
            ->get()
            ->map(fn (Novedad $novedad) => [
                'id' => $novedad->id,
                'title' => $novedad->getNormalizedTranslations('title'),
                'category' => $novedad->getNormalizedTranslations('category'),
                'excerpt' => $novedad->getNormalizedTranslations('excerpt'),
                'body' => $novedad->getNormalizedTranslations('body'),
                'image' => $novedad->image,
                'image_url' => $novedad->image_url,
                'is_active' => $novedad->is_active,
                'created_at' => optional($novedad->created_at)?->toISOString(),
                'updated_at' => optional($novedad->updated_at)?->toISOString(),
            ])
            ->values();

        return Inertia::render('Admin/PrivateZone/Novedades', [
            'novedades' => $novedades,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image'     => ['nullable', 'image', 'max:3072'],
            'is_active' => ['boolean'],

            // categoría (3 idiomas)
            'category_es' => ['nullable', 'string', 'max:100'],
            'category_en' => ['nullable', 'string', 'max:100'],
            'category_pt' => ['nullable', 'string', 'max:100'],

            // título (3 idiomas)
            'title_es'  => ['required', 'string', 'max:255'],
            'title_en'  => ['nullable', 'string', 'max:255'],
            'title_pt'  => ['nullable', 'string', 'max:255'],

            // extracto (3 idiomas)
            'excerpt_es' => ['nullable', 'string'],
            'excerpt_en' => ['nullable', 'string'],
            'excerpt_pt' => ['nullable', 'string'],

            // cuerpo HTML (3 idiomas)
            'body_es'   => ['nullable', 'string'],
            'body_en'   => ['nullable', 'string'],
            'body_pt'   => ['nullable', 'string'],
        ]);

        $payload = [
            'category' => [
                'es' => $data['category_es'] ?? '',
                'en' => $data['category_en'] ?? '',
                'pt' => $data['category_pt'] ?? '',
            ],
            'is_active' => $data['is_active'] ?? true,

            'title'   => [
                'es' => $data['title_es'],
                'en' => $data['title_en'] ?? $data['title_es'],
                'pt' => $data['title_pt'] ?? $data['title_es'],
            ],
            'excerpt' => [
                'es' => $data['excerpt_es'] ?? '',
                'en' => $data['excerpt_en'] ?? '',
                'pt' => $data['excerpt_pt'] ?? '',
            ],
            'body'    => [
                'es' => $data['body_es'] ?? '',
                'en' => $data['body_en'] ?? '',
                'pt' => $data['body_pt'] ?? '',
            ],
        ];

        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('novedades', 'public');
        }

        Novedad::create($payload);

        return back()->with('success', 'Novedad creada.');
    }

    public function update(Request $request, Novedad $novedad)
    {
        $data = $request->validate([
            'image'     => ['nullable', 'image', 'max:3072'],
            'is_active' => ['boolean'],

            // categoría (3 idiomas)
            'category_es' => ['nullable', 'string', 'max:100'],
            'category_en' => ['nullable', 'string', 'max:100'],
            'category_pt' => ['nullable', 'string', 'max:100'],

            'title_es'  => ['required', 'string', 'max:255'],
            'title_en'  => ['nullable', 'string', 'max:255'],
            'title_pt'  => ['nullable', 'string', 'max:255'],

            'excerpt_es' => ['nullable', 'string'],
            'excerpt_en' => ['nullable', 'string'],
            'excerpt_pt' => ['nullable', 'string'],

            'body_es'   => ['nullable', 'string'],
            'body_en'   => ['nullable', 'string'],
            'body_pt'   => ['nullable', 'string'],
        ]);

        $payload = [
            'category' => [
                'es' => $data['category_es'] ?? '',
                'en' => $data['category_en'] ?? '',
                'pt' => $data['category_pt'] ?? '',
            ],
            'is_active' => $data['is_active'] ?? true,

            'title'   => [
                'es' => $data['title_es'],
                'en' => $data['title_en'] ?? $data['title_es'],
                'pt' => $data['title_pt'] ?? $data['title_es'],
            ],
            'excerpt' => [
                'es' => $data['excerpt_es'] ?? '',
                'en' => $data['excerpt_en'] ?? '',
                'pt' => $data['excerpt_pt'] ?? '',
            ],
            'body'    => [
                'es' => $data['body_es'] ?? '',
                'en' => $data['body_en'] ?? '',
                'pt' => $data['body_pt'] ?? '',
            ],
        ];

        if ($request->hasFile('image')) {
            if ($novedad->image) {
                Storage::disk('public')->delete($novedad->image);
            }
            $payload['image'] = $request->file('image')->store('novedades', 'public');
        }

        $novedad->update($payload);

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
