<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PublicNovedad;
use App\Models\PublicNovedadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicNovedadAdminController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'image'     => ['nullable', 'image', 'max:3072'],
            'gallery_images'   => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:3072'],
            'is_active' => ['boolean'],
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
            'title' => [
                'es' => $data['title_es'],
                'en' => $data['title_en'] ?? $data['title_es'],
                'pt' => $data['title_pt'] ?? $data['title_es'],
            ],
            'excerpt' => [
                'es' => $data['excerpt_es'] ?? '',
                'en' => $data['excerpt_en'] ?? '',
                'pt' => $data['excerpt_pt'] ?? '',
            ],
            'body' => [
                'es' => $data['body_es'] ?? '',
                'en' => $data['body_en'] ?? '',
                'pt' => $data['body_pt'] ?? '',
            ],
        ];

        if ($request->hasFile('image')) {
            $payload['image'] = $request->file('image')->store('public-novedades', 'public');
        }

        $novedad = PublicNovedad::create($payload);

        foreach ($request->file('gallery_images', []) as $index => $image) {
            $novedad->galleryImages()->create([
                'image' => $image->store('public-novedades', 'public'),
                'order' => sprintf('%03d', $index + 1),
            ]);
        }

        return back()->with('success', 'Novedad pública creada.');
    }

    public function update(Request $request, PublicNovedad $novedad)
    {
        $data = $request->validate([
            'image'     => ['nullable', 'image', 'max:3072'],
            'gallery_images'   => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:3072'],
            'is_active' => ['boolean'],
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
            'title' => [
                'es' => $data['title_es'],
                'en' => $data['title_en'] ?? $data['title_es'],
                'pt' => $data['title_pt'] ?? $data['title_es'],
            ],
            'excerpt' => [
                'es' => $data['excerpt_es'] ?? '',
                'en' => $data['excerpt_en'] ?? '',
                'pt' => $data['excerpt_pt'] ?? '',
            ],
            'body' => [
                'es' => $data['body_es'] ?? '',
                'en' => $data['body_en'] ?? '',
                'pt' => $data['body_pt'] ?? '',
            ],
        ];

        if ($request->hasFile('image')) {
            if ($novedad->image) {
                Storage::disk('public')->delete($novedad->image);
            }

            $payload['image'] = $request->file('image')->store('public-novedades', 'public');
        }

        $novedad->update($payload);

        $currentCount = $novedad->galleryImages()->count();
        foreach ($request->file('gallery_images', []) as $offset => $image) {
            $novedad->galleryImages()->create([
                'image' => $image->store('public-novedades', 'public'),
                'order' => sprintf('%03d', $currentCount + $offset + 1),
            ]);
        }

        return back()->with('success', 'Novedad pública actualizada.');
    }

    public function destroyImage(PublicNovedadImage $image)
    {
        if ($image->image) {
            Storage::disk('public')->delete($image->image);
        }

        $image->delete();

        return back()->with('success', 'Imagen adicional eliminada.');
    }

    public function destroy(PublicNovedad $novedad)
    {
        if ($novedad->image) {
            Storage::disk('public')->delete($novedad->image);
        }

        foreach ($novedad->galleryImages as $image) {
            if ($image->image) {
                Storage::disk('public')->delete($image->image);
            }
        }

        $novedad->delete();

        return back()->with('success', 'Novedad pública eliminada.');
    }
}
