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
        ]);

        $meta = SeoMeta::firstOrNew(['page' => $data['page']]);

        if ($request->hasFile('og_image')) {
            if ($meta->og_image) Storage::disk('public')->delete($meta->og_image);
            $meta->og_image = $request->file('og_image')->store('seo', 'public');
        }

        $meta->title       = $data['title'] ?? $meta->title;
        $meta->description = $data['description'] ?? $meta->description;
        $meta->keywords    = $data['keywords'] ?? $meta->keywords;
        $meta->save();

        return back()->with('success', 'Meta SEO actualizado.');
    }
}
