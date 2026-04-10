<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SeoController extends Controller
{
    /** Pages to manage SEO for */
    private const PAGES = [
        'home'      => 'Inicio',
        'productos' => 'Productos',
        'empresa'   => 'Empresa',
        'calidad'   => 'Calidad',
        'contacto'  => 'Contacto',
    ];

    public function index()
    {
        $metas = SeoMeta::all()->keyBy('page');

        $pages = collect(self::PAGES)->map(function ($label, $key) use ($metas) {
            $meta = $metas->get($key);
            return [
                'key'         => $key,
                'label'       => $label,
                'title'       => $meta?->title,
                'description' => $meta?->description,
                'keywords'    => $meta?->keywords,
                'og_image_url' => $meta?->og_image_url,
            ];
        })->values();

        return Inertia::render('Admin/Seo/Index', ['pages' => $pages]);
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'page'        => 'required|string|max:100',
            'title'       => 'nullable|string|max:200',
            'description' => 'nullable|string|max:500',
            'keywords'    => 'nullable|string|max:500',
            'og_image'    => 'nullable|image|max:4096',
            'remove_og_image' => 'nullable|boolean',
        ]);

        $meta = SeoMeta::firstOrNew(['page' => $data['page']]);
        $removeOgImage = $request->boolean('remove_og_image');

        if ($removeOgImage && $meta->og_image) {
            Storage::disk('public')->delete($meta->og_image);
            $meta->og_image = null;
        }

        if ($request->hasFile('og_image')) {
            if ($meta->og_image) Storage::disk('public')->delete($meta->og_image);
            $meta->og_image = $request->file('og_image')->store('seo', 'public');
        }

        $meta->title       = $this->normalizeMetaValue($data['title'] ?? null);
        $meta->description = $this->normalizeMetaValue($data['description'] ?? null);
        $meta->keywords    = $this->normalizeMetaValue($data['keywords'] ?? null);

        if (!$meta->title && !$meta->description && !$meta->keywords && !$meta->og_image) {
            if ($meta->exists) {
                $meta->delete();
            }

            return back()->with('success', 'Meta SEO eliminado.');
        }

        $meta->save();

        return back()->with('success', 'Meta SEO actualizado.');
    }

    private function normalizeMetaValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
