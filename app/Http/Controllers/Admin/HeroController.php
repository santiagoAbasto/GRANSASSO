<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Traits\SanitizesHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
            'media_type'  => 'required|in:image,youtube,video',
            'image'       => 'nullable|image|max:5120',
            'video_url'   => 'nullable|url|max:500',
            'video_file'  => 'nullable|file|mimes:mp4,mov,webm|max:51200',
            'order'       => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);

        $this->validateHeroMedia($request, $data['media_type']);

        $buttonLink = $this->sanitizeUrl($data['button_link'] ?? null, allowRelative: true);
        $videoUrl = $data['media_type'] === 'youtube'
            ? $this->sanitizeUrl($data['video_url'] ?? null)
            : null;

        $imagePath = null;
        $videoFilePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('sliders', 'public');
        }
        if ($request->hasFile('video_file')) {
            $videoFilePath = $request->file('video_file')->store('sliders/videos', 'public');
        }

        Slider::create([
            'title'       => ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null],
            'subtitle'    => ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null],
            'button_text' => ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null],
            'button_link' => $buttonLink,
            'image'       => $data['media_type'] === 'image' ? $imagePath : null,
            'video_url'   => $videoUrl,
            'video_file'  => $data['media_type'] === 'video' ? $videoFilePath : null,
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
                'video_url'      => $hero->video_url,
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
            'media_type'  => 'required|in:image,youtube,video',
            'image'       => 'nullable|image|max:5120',
            'video_url'   => 'nullable|url|max:500',
            'video_file'  => 'nullable|file|mimes:mp4,mov,webm|max:51200',
            'order'       => 'nullable|string|max:20',
            'is_active'   => 'boolean',
        ]);

        $this->validateHeroMedia($request, $data['media_type'], $hero);

        $buttonLink = array_key_exists('button_link', $data)
            ? $this->sanitizeUrl($data['button_link'], allowRelative: true)
            : $hero->button_link;
        $videoUrl = $data['media_type'] === 'youtube'
            ? $this->sanitizeUrl($data['video_url'] ?? null)
            : null;

        $imagePath = $hero->image;
        $videoFilePath = $hero->video_file;

        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('sliders', 'public');
        }

        if ($request->hasFile('video_file')) {
            if ($videoFilePath) {
                Storage::disk('public')->delete($videoFilePath);
            }
            $videoFilePath = $request->file('video_file')->store('sliders/videos', 'public');
        }

        if ($data['media_type'] !== 'image' && $hero->image) {
            Storage::disk('public')->delete($hero->image);
            $imagePath = null;
        }

        if ($data['media_type'] !== 'video' && $hero->video_file) {
            Storage::disk('public')->delete($hero->video_file);
            $videoFilePath = null;
        }

        $hero->update([
            'title'       => ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null],
            'subtitle'    => ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null],
            'button_text' => ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null],
            'button_link' => $buttonLink,
            'image'       => $data['media_type'] === 'image' ? $imagePath : null,
            'video_url'   => $videoUrl,
            'video_file'  => $data['media_type'] === 'video' ? $videoFilePath : null,
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
        if ($hero->video_file) {
            Storage::disk('public')->delete($hero->video_file);
        }
        $hero->delete();

        return back()->with('success', 'Slide eliminado.');
    }

    private function validateHeroMedia(Request $request, string $mediaType, ?Slider $hero = null): void
    {
        if ($mediaType === 'image' && ! $request->hasFile('image') && ! $hero?->image) {
            throw ValidationException::withMessages([
                'image' => 'Se requiere una imagen para este slide.',
            ]);
        }

        if ($mediaType === 'youtube' && ! $request->filled('video_url') && ! $hero?->video_url) {
            throw ValidationException::withMessages([
                'video_url' => 'Se requiere un link de YouTube para este slide.',
            ]);
        }

        if ($mediaType === 'youtube' && $request->filled('video_url') && ! $this->looksLikeYouTubeUrl($request->input('video_url'))) {
            throw ValidationException::withMessages([
                'video_url' => 'El link debe ser de YouTube.',
            ]);
        }

        if ($mediaType === 'video' && ! $request->hasFile('video_file') && ! $hero?->video_file) {
            throw ValidationException::withMessages([
                'video_file' => 'Se requiere un video local para este slide.',
            ]);
        }
    }

    private function looksLikeYouTubeUrl(?string $url): bool
    {
        $parts = parse_url((string) $url);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'], true)) {
            return false;
        }

        parse_str($parts['query'] ?? '', $query);
        $videoId = null;

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $videoId = trim($parts['path'] ?? '', '/');
        } elseif (($query['v'] ?? null)) {
            $videoId = $query['v'];
        } elseif (preg_match('#^/(embed|shorts)/([a-zA-Z0-9_-]{11})#', $parts['path'] ?? '', $matches)) {
            $videoId = $matches[2];
        }

        return (bool) preg_match('/^[a-zA-Z0-9_-]{11}$/', (string) $videoId);
    }
}
