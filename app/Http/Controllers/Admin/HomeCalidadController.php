<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionImage;
use App\Traits\SanitizesHtml;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeCalidadController extends Controller
{
    use SanitizesHtml;
    /*
    |--------------------------------------------------------------------------
    | EDIT / SHOW
    |--------------------------------------------------------------------------
    */

    public function edit()
    {
        $page    = Page::where('slug', 'home')->firstOrFail();
        $section = $page->sections()
                        ->with('images')
                        ->where('type', 'quality')
                        ->first();

        $sectionData = null;

        if ($section) {
            $sectionData = [
                'id'         => $section->id,
                'title'      => $section->title      ?? ['es' => '', 'en' => '', 'pt' => ''],
                'content'    => $section->content    ?? ['es' => '', 'en' => '', 'pt' => ''],
                'button_text'=> $section->button_text ?? ['es' => '', 'en' => '', 'pt' => ''],
                'button_link'=> $section->button_link ?? '',
                'is_active'  => $section->is_active,
                'image_url'  => $section->image ? asset('storage/' . $section->image) : null,
                'images'     => $section->images->sortBy('order')->map(fn ($img) => [
                    'id'        => $img->id,
                    'image_url' => asset('storage/' . $img->image),
                    'order'     => $img->order,
                ])->values(),
            ];
        }

        return Inertia::render('Admin/CMS/HomeCalidad', [
            'page'    => ['id' => $page->id, 'slug' => $page->slug],
            'section' => $sectionData,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {
        $data = $request->validate([
            'title_es'       => 'required|string|max:255',
            'title_en'       => 'nullable|string|max:255',
            'title_pt'       => 'nullable|string|max:255',
            'content_es'     => 'required|string',
            'content_en'     => 'nullable|string',
            'content_pt'     => 'nullable|string',
            'button_text_es' => 'nullable|string|max:100',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_pt' => 'nullable|string|max:100',
            'button_link'    => 'nullable|string|max:500',
            'is_active'      => 'boolean',
            'image'          => 'nullable|image|max:5120',
            'image_1'        => 'nullable|image|max:3072',
            'image_2'        => 'nullable|image|max:3072',
            'image_3'        => 'nullable|image|max:3072',
        ]);

        $data = $this->sanitizeDataFields($data, ['title', 'content', 'button_text']);

        $page    = Page::where('slug', 'home')->firstOrFail();
        $section = $page->sections()->where('type', 'quality')->first();

        // Background image
        $imagePath = $section?->image;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('quality', 'public');
        }

        $payload = [
            'page_id'     => $page->id,
            'type'        => 'quality',
            'title'       => [
                'es' => $data['title_es'],
                'en' => $data['title_en'] ?? '',
                'pt' => $data['title_pt'] ?? '',
            ],
            'content'     => [
                'es' => $data['content_es'],
                'en' => $data['content_en'] ?? '',
                'pt' => $data['content_pt'] ?? '',
            ],
            'button_text' => [
                'es' => $data['button_text_es'] ?? '',
                'en' => $data['button_text_en'] ?? '',
                'pt' => $data['button_text_pt'] ?? '',
            ],
            'button_link' => $data['button_link'] ?? '',
            'is_active'   => $data['is_active'] ?? true,
            'image'       => $imagePath,
            'order'       => 2,
        ];

        if ($section) {
            $section->update($payload);
        } else {
            $section = PageSection::create($payload);
        }

        // Imágenes adicionales (logos / certificaciones) — slots 1, 2, 3
        foreach ([1, 2, 3] as $slot) {
            $key = "image_{$slot}";
            if ($request->hasFile($key)) {
                $path     = $request->file($key)->store('quality', 'public');
                $existing = $section->images()->where('order', $slot)->first();
                if ($existing) {
                    $existing->update(['image' => $path]);
                } else {
                    $section->images()->create(['image' => $path, 'order' => $slot]);
                }
            }
        }

        return back()->with('success', 'Sección Calidad actualizada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE IMAGE (imagen adicional individual)
    |--------------------------------------------------------------------------
    */

    public function destroyImage(PageSectionImage $image)
    {
        $image->delete();
        return back()->with('success', 'Imagen eliminada.');
    }
}
