<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\SanitizesHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class HeroController extends Controller
{
    use SanitizesHtml;

    public function index()
    {
        $slides = Slider::ordered()->get()->map(function ($s) {
            $str = fn ($field) => is_array($s->$field) ? ($s->$field['es'] ?? '') : ($s->$field ?? '');
            return [
                'id'          => $s->id,
                'title'       => $str('title'),
                'subtitle'    => $str('subtitle'),
                'button_text' => $str('button_text'),
                'button_link' => $s->button_link,
                'order'       => $s->order,
                'is_active'   => $s->is_active,
                'media_url'   => $s->media_url,
                'media_type'  => $s->media_type,
            ];
        });

        return Inertia::render('Admin/Hero/Index', compact('slides'));
    }

    public function create()
    {
        return Inertia::render('Admin/Hero/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title_es'    => 'nullable|string|max:200',
            'title_en'    => 'nullable|string|max:200',
            'title_pt'    => 'nullable|string|max:200',
            'subtitle_es' => 'nullable|string|max:300',
            'subtitle_en' => 'nullable|string|max:300',
            'subtitle_pt' => 'nullable|string|max:300',
            'button_text_es' => 'nullable|string|max:100',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_pt' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'image'       => 'nullable|image|max:5120',
            'order'       => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);
        $buttonLink = $this->sanitizeUrl($data['button_link'] ?? null, allowRelative: true);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sliders', 'public');
        }

        Slider::create([
            'title'       => ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null],
            'subtitle'    => ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null],
            'button_text' => ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null],
            'button_link' => $buttonLink,
            'image'       => $imagePath,
            'order'       => strtoupper(trim($data['order'] ?? 'A')),
            'is_active'   => $data['is_active'] ?? true,
        ]);

        return redirect()->route('admin.hero.index')->with('success', 'Slide creado correctamente.');
    }

    public function edit(Slider $hero)
    {
        $str = fn ($field) => is_array($hero->$field) ? ($hero->$field['es'] ?? '') : ($hero->$field ?? '');
        return Inertia::render('Admin/Hero/Edit', [
            'slide' => [
                'id'             => $hero->id,
                'title_es'       => is_array($hero->title)    ? ($hero->title['es']       ?? '') : '',
                'title_en'       => is_array($hero->title)    ? ($hero->title['en']       ?? '') : '',
                'title_pt'       => is_array($hero->title)    ? ($hero->title['pt']       ?? '') : '',
                'subtitle_es'    => is_array($hero->subtitle) ? ($hero->subtitle['es']    ?? '') : '',
                'subtitle_en'    => is_array($hero->subtitle) ? ($hero->subtitle['en']    ?? '') : '',
                'subtitle_pt'    => is_array($hero->subtitle) ? ($hero->subtitle['pt']    ?? '') : '',
                'button_text_es' => is_array($hero->button_text) ? ($hero->button_text['es'] ?? '') : '',
                'button_text_en' => is_array($hero->button_text) ? ($hero->button_text['en'] ?? '') : '',
                'button_text_pt' => is_array($hero->button_text) ? ($hero->button_text['pt'] ?? '') : '',
                'button_link'    => $hero->button_link,
                'order'          => $hero->order,
                'is_active'      => $hero->is_active,
                'media_url'      => $hero->media_url,
                'media_type'     => $hero->media_type,
            ],
        ]);
    }

    public function update(Request $request, Slider $hero)
    {
        $data = $request->validate([
            'title_es'    => 'nullable|string|max:200',
            'title_en'    => 'nullable|string|max:200',
            'title_pt'    => 'nullable|string|max:200',
            'subtitle_es' => 'nullable|string|max:300',
            'subtitle_en' => 'nullable|string|max:300',
            'subtitle_pt' => 'nullable|string|max:300',
            'button_text_es' => 'nullable|string|max:100',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_pt' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'image'       => 'nullable|image|max:5120',
            'order'       => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);
        $buttonLink = array_key_exists('button_link', $data)
            ? $this->sanitizeUrl($data['button_link'], allowRelative: true)
            : $hero->button_link;

        $imagePath = $hero->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('sliders', 'public');
        }

        $hero->update([
            'title'       => ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null],
            'subtitle'    => ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null],
            'button_text' => ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null],
            'button_link' => $buttonLink,
            'image'       => $imagePath,
            'order'       => strtoupper(trim($data['order'] ?? $hero->order)),
            'is_active'   => $data['is_active'] ?? $hero->is_active,
        ]);

        return redirect()->route('admin.hero.index')->with('success', 'Slide actualizado correctamente.');
    }

    public function toggle(Slider $hero)
    {
        $hero->update(['is_active' => ! $hero->is_active]);
        return back()->with('success', $hero->is_active ? 'Slide activado.' : 'Slide desactivado.');
    }

    public function destroy(Slider $hero)
    {
        if ($hero->image) {
            Storage::disk('public')->delete($hero->image);
        }
        $hero->delete();

        return back()->with('success', 'Slide eliminado.');
    }
}
