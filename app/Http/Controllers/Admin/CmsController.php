<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionImage;
use App\Traits\SanitizesHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * Gestiona el contenido CMS de páginas estáticas (empresa, calidad, contacto, home-about).
 */
class CmsController extends Controller
{
    use SanitizesHtml;
    // ── HOME → bloque "Nosotros" ─────────────────────────────────────────

    public function homeAbout()
    {
        $page    = Page::firstOrCreate(['slug' => 'home'], ['title' => ['es' => 'Inicio', 'en' => 'Home', 'pt' => 'Início']]);
        $section = $page->sections()->where('type', 'about')->first();

        return Inertia::render('Admin/CMS/HomeAbout', [
            'page'    => $page,
            'section' => $section ? $this->expandSection($section) : null,
        ]);
    }

    // ── PRODUCTOS → Banner ───────────────────────────────────────────────

    public function productsBanner()
    {
        $page = Page::firstOrCreate(
            ['slug' => 'products'],
            ['title' => ['es' => 'Productos', 'en' => 'Products', 'pt' => 'Produtos']]
        );

        // Auto-create the banner section so images can always be added
        $section = $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 'A']
        );

        $section->load('images');

        return Inertia::render('Admin/CMS/ProductsBanner', [
            'section' => [
                'id'        => $section->id,
                'is_active' => $section->is_active,
                'slides'    => $section->images->sortBy('order')->map(fn ($img) => [
                    'id'        => $img->id,
                    'image_url' => $img->image ? asset('storage/' . $img->image) : null,
                    'video_url' => $img->video_url,
                    'order'     => $img->order,
                ])->values(),
            ],
        ]);
    }

    // ── EMPRESA ──────────────────────────────────────────────────────────

    public function empresa()
    {
        $page = Page::where('slug', 'empresa')
            ->with(['sections' => fn ($q) => $q->orderBy('order')->with('images')])
            ->firstOrCreate(
                ['slug' => 'empresa'],
                ['title' => ['es' => 'Empresa', 'en' => 'Company', 'pt' => 'Empresa']]
            );

        $sections = $page->sections->map(fn ($s) => $this->expandSection($s, withImages: true))->values();

        return Inertia::render('Admin/CMS/Empresa', ['page' => $page, 'sections' => $sections]);
    }

    // ── CALIDAD ──────────────────────────────────────────────────────────

    public function calidad()
    {
        $page = Page::where('slug', 'calidad')
            ->with(['sections' => fn ($q) => $q->orderBy('order')->with('images')])
            ->firstOrCreate(
                ['slug' => 'calidad'],
                ['title' => ['es' => 'Calidad', 'en' => 'Quality', 'pt' => 'Qualidade']]
            );

        $sections = $page->sections->map(fn ($s) => $this->flattenSection($s))->values();

        return Inertia::render('Admin/CMS/Calidad', ['page' => $page, 'sections' => $sections]);
    }

    // ── CONTACTO → info (dirección, teléfono, email, mapa) ───────────────

    public function contacto()
    {
        $page = Page::where('slug', 'contacto')
            ->with(['sections' => fn ($q) => $q->orderBy('order')])
            ->firstOrCreate(
                ['slug' => 'contacto'],
                ['title' => ['es' => 'Contacto', 'en' => 'Contact', 'pt' => 'Contato']]
            );

        $infoSection = $page->sections()->firstOrCreate(
            ['type' => 'contact_info'],
            [
                'order'     => 1,
                'is_active' => true,
                'title'     => ['es' => 'Datos de contacto', 'en' => 'Contact info', 'pt' => 'Dados de contato'],
                'content'   => ['address' => '', 'phone' => '', 'email' => '', 'map_embed' => ''],
            ]
        );

        $contactInfo = array_merge(
            ['address' => '', 'phone' => '', 'email' => '', 'map_embed' => ''],
            is_array($infoSection->content) ? $infoSection->content : []
        );

        return Inertia::render('Admin/CMS/Contacto', [
            'page'        => $page,
            'contactInfo' => $contactInfo,
        ]);
    }

    // ── DESARROLLOS ESPECIALES ───────────────────────────────────────────

    public function desarrollos()
    {
        $page = Page::where('slug', 'desarrollos-especiales')
            ->with(['sections' => fn ($q) => $q->orderBy('order')->with('images')])
            ->firstOrCreate(
                ['slug' => 'desarrollos-especiales'],
                ['title' => ['es' => 'Desarrollos especiales', 'en' => 'Special Developments', 'pt' => 'Desenvolvimentos especiais']]
            );

        // Auto-create banner section
        $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 'A']
        );

        // Auto-create gallery section
        $page->sections()->firstOrCreate(
            ['type' => 'desarrollos_gallery'],
            ['is_active' => true, 'order' => 'B']
        );

        $page->load(['sections' => fn ($q) => $q->orderBy('order')->with('images')]);

        $sections = $page->sections->map(fn ($s) => $this->expandSection($s, withImages: true))->values();

        return Inertia::render('Admin/CMS/Desarrollos', ['page' => $page, 'sections' => $sections]);
    }

    // ── GENERIC: crear una sección en una página ─────────────────────────

    public function createSection(Request $request, Page $page)
    {
        $data = $request->validate([
            'type'           => 'required|string|max:100',
            'title_es'       => 'nullable|string',
            'title_en'       => 'nullable|string',
            'title_pt'       => 'nullable|string',
            'subtitle_es'    => 'nullable|string',
            'subtitle_en'    => 'nullable|string',
            'subtitle_pt'    => 'nullable|string',
            'content_es'     => 'nullable|string',
            'content_en'     => 'nullable|string',
            'content_pt'     => 'nullable|string',
            'button_text_es' => 'nullable|string|max:100',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_pt' => 'nullable|string|max:100',
            'button_link'    => 'nullable|string|max:500',
            'order'          => 'nullable|string|max:20',
            'is_active'      => 'boolean',
            'image'          => 'nullable|image|max:4096',
        ]);

        $data = $this->sanitizeDataFields($data, ['title', 'subtitle', 'content', 'button_text']);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('pages', 'public');
        }

        $page->sections()->create([
            'type'        => $data['type'],
            'title'       => ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null],
            'subtitle'    => ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null],
            'content'     => ['es' => $data['content_es'] ?? null, 'en' => $data['content_en'] ?? null, 'pt' => $data['content_pt'] ?? null],
            'button_text' => ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null],
            'button_link' => $data['button_link'] ?? null,
            'order'       => strtoupper(trim($data['order'] ?? 'A')),
            'is_active'   => $data['is_active'] ?? true,
            'image'       => $imagePath,
        ]);

        return back()->with('success', 'Sección creada.');
    }

    // ── GENERIC: actualizar una sección ──────────────────────────────────

    public function updateSection(Request $request, PageSection $section)
    {
        $data = $request->validate([
            'title_es'       => 'nullable|string',
            'title_en'       => 'nullable|string',
            'title_pt'       => 'nullable|string',
            'subtitle_es'    => 'nullable|string',
            'subtitle_en'    => 'nullable|string',
            'subtitle_pt'    => 'nullable|string',
            'content_es'     => 'nullable|string',
            'content_en'     => 'nullable|string',
            'content_pt'     => 'nullable|string',
            'button_text_es' => 'nullable|string|max:100',
            'button_text_en' => 'nullable|string|max:100',
            'button_text_pt' => 'nullable|string|max:100',
            'button_link'    => 'nullable|string|max:500',
            'order'          => 'nullable|string|max:20',
            'is_active'      => 'boolean',
            'image'          => 'nullable|image|max:4096',
        ]);

        $data = $this->sanitizeDataFields($data, ['title', 'subtitle', 'content', 'button_text']);

        $imagePath = $section->image;
        if ($request->hasFile('image')) {
            if ($imagePath) Storage::disk('public')->delete($imagePath);
            $imagePath = $request->file('image')->store('pages', 'public');
        }

        $existing = fn ($field) => is_array($section->$field)
            ? $section->$field
            : ['es' => null, 'en' => null, 'pt' => null];

        $section->update([
            'title'       => array_merge($existing('title'),       ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null]),
            'subtitle'    => array_merge($existing('subtitle'),    ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null]),
            'content'     => array_merge($existing('content'),     ['es' => $data['content_es'] ?? null, 'en' => $data['content_en'] ?? null, 'pt' => $data['content_pt'] ?? null]),
            'button_text' => array_merge($existing('button_text'), ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null]),
            'button_link' => $data['button_link'] ?? $section->button_link,
            'order'       => isset($data['order']) ? strtoupper(trim($data['order'])) : $section->order,
            'is_active'   => $data['is_active']   ?? $section->is_active,
            'image'       => $imagePath,
        ]);

        return back()->with('success', 'Sección actualizada.');
    }

    // ── Agregar imagen a una sección (banner, galería, etc.) ─────────────

    public function addSectionImage(Request $request, PageSection $section)
    {
        $request->validate([
            'image'     => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:500',
            'order'     => 'nullable|string|max:20',
        ]);

        if (! $request->hasFile('image') && ! $request->filled('video_url')) {
            return back()->withErrors(['image' => 'Se requiere una imagen o URL de video.']);
        }

        // Calcular siguiente orden alfanumérico si no se provee uno
        $order = $request->filled('order')
            ? strtoupper(trim($request->input('order')))
            : $this->nextAlphaOrder($section->images()->pluck('order')->toArray());

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('pages/banner', 'public');
        }

        $section->images()->create([
            'image'     => $path,
            'video_url' => $request->filled('video_url') ? $request->input('video_url') : null,
            'order'     => $order,
        ]);

        return back()->with('success', 'Slide agregado.');
    }

    // ── Actualizar un slide (orden + imagen o video) ──────────────────────

    public function updateSectionImage(Request $request, PageSectionImage $image)
    {
        $request->validate([
            'order'     => 'nullable|string|max:20',
            'image'     => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:500',
        ]);

        $path = $image->image;
        if ($request->hasFile('image')) {
            if ($path) Storage::disk('public')->delete($path);
            $path = $request->file('image')->store('pages/banner', 'public');
        }

        $image->update([
            'order'     => $request->filled('order') ? strtoupper(trim($request->input('order'))) : $image->order,
            'image'     => $path,
            'video_url' => $request->has('video_url') ? ($request->filled('video_url') ? $request->input('video_url') : null) : $image->video_url,
        ]);

        return back()->with('success', 'Slide actualizado.');
    }

    // ── Helper: siguiente orden alfanumérico (A→B→...→Z→AA→AB...) ────────

    private function nextAlphaOrder(array $existing): string
    {
        $existing = array_map('strtoupper', array_filter($existing));

        if (empty($existing)) return 'A';

        // Función para incrementar una cadena alfabética
        $increment = function (string $s): string {
            $s = strtoupper($s);
            $i = strlen($s) - 1;
            while ($i >= 0) {
                if ($s[$i] < 'Z') {
                    $s[$i] = chr(ord($s[$i]) + 1);
                    return $s;
                }
                $s[$i] = 'A';
                $i--;
            }
            return 'A' . $s; // overflow: Z→AA, AZ→BA, etc.
        };

        // Ordenar las existentes alfabéticamente y tomar la última
        usort($existing, 'strcmp');
        $last = end($existing);

        // Incrementar hasta encontrar una libre
        $next = $increment($last);
        while (in_array($next, $existing, true)) {
            $next = $increment($next);
        }

        return $next;
    }

    // ── Eliminar una imagen de sección ────────────────────────────────────

    public function destroySectionImage(PageSectionImage $image)
    {
        if ($image->image) {
            Storage::disk('public')->delete($image->image);
        }
        $image->delete();
        return back()->with('success', 'Slide eliminado.');
    }

    // ── GENERIC: toggle is_active ─────────────────────────────────────────

    public function toggleSection(PageSection $section)
    {
        $section->update(['is_active' => ! $section->is_active]);
        return back()->with('success', $section->is_active ? 'Sección activada.' : 'Sección desactivada.');
    }

    // ── GENERIC: eliminar una sección ────────────────────────────────────

    public function destroySection(PageSection $section)
    {
        if ($section->image) {
            Storage::disk('public')->delete($section->image);
        }
        $section->delete();
        return back()->with('success', 'Sección eliminada.');
    }

    // ── Actualizar info de contacto (JSON en content) ─────────────────────

    public function updateContactInfo(Request $request)
    {
        $data = $request->validate([
            'address'   => 'nullable|string|max:500',
            'phone'     => 'nullable|string|max:100',
            'email'     => 'nullable|email|max:200',
            'map_embed' => 'nullable|string',
        ]);

        $page = Page::where('slug', 'contacto')->firstOrFail();
        $section = $page->sections()->firstOrCreate(['type' => 'contact_info'], ['order' => 1, 'is_active' => true]);

        $section->update(['content' => $data]);

        return back()->with('success', 'Datos de contacto actualizados.');
    }

    // ── Helper: expandir sección (JSON fields → es/en/pt separados) ──────

    private function expandSection(PageSection $section, bool $withImages = false): array
    {
        $get = fn ($field, $lang) => is_array($section->$field)
            ? ($section->$field[$lang] ?? '')
            : ($lang === 'es' ? ($section->$field ?? '') : '');

        $result = [
            'id'             => $section->id,
            'type'           => $section->type,
            'title_es'       => $get('title', 'es'),
            'title_en'       => $get('title', 'en'),
            'title_pt'       => $get('title', 'pt'),
            'subtitle_es'    => $get('subtitle', 'es'),
            'subtitle_en'    => $get('subtitle', 'en'),
            'subtitle_pt'    => $get('subtitle', 'pt'),
            'content_es'     => $get('content', 'es'),
            'content_en'     => $get('content', 'en'),
            'content_pt'     => $get('content', 'pt'),
            'button_text_es' => $get('button_text', 'es'),
            'button_text_en' => $get('button_text', 'en'),
            'button_text_pt' => $get('button_text', 'pt'),
            'button_link'    => $section->button_link,
            'order'          => $section->order,
            'is_active'      => $section->is_active,
            'image_url'      => $section->image ? asset('storage/' . $section->image) : null,
        ];

        if ($withImages) {
            $result['images'] = $section->images->sortBy('order')->map(fn ($img) => [
                'id'        => $img->id,
                'image_url' => asset('storage/' . $img->image),
                'order'     => $img->order,
            ])->values();
        }

        return $result;
    }

    // ── Helper: aplanar sección (JSON fields → ES por defecto) ───────────

    private function flattenSection(PageSection $section): array
    {
        $str = fn ($field) => is_array($section->$field)
            ? ($section->$field['es'] ?? '')
            : ($section->$field ?? '');

        return [
            'id'          => $section->id,
            'type'        => $section->type,
            'title'       => $str('title'),
            'subtitle'    => $str('subtitle'),
            'content'     => $str('content'),
            'button_text' => $str('button_text'),
            'button_link' => $section->button_link,
            'order'       => $section->order,
            'is_active'   => $section->is_active,
            'image_url'   => $section->image ? asset('storage/' . $section->image) : null,
        ];
    }
}
