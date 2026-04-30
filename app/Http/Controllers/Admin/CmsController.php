<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalidadDownload;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionImage;
use App\Models\PublicNovedad;
use App\Traits\SanitizesHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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

    // ── HOME → galería de imágenes destacadas ────────────────────────────

    public function homeGallery()
    {
        $page = Page::firstOrCreate(
            ['slug' => 'home'],
            ['title' => ['es' => 'Inicio', 'en' => 'Home', 'pt' => 'Início']]
        );

        $section = $page->sections()->firstOrCreate(
            ['type' => 'home_gallery'],
            ['is_active' => true, 'order' => 'C']
        );

        $section->load('images');

        return Inertia::render('Admin/CMS/HomeGallery', [
            'section' => [
                'id'        => $section->id,
                'is_active' => $section->is_active,
                'images'    => $section->images->sortBy('order')->map(fn ($img) => [
                    'id'        => $img->id,
                    'image_url' => $img->image ? asset('storage/' . $img->image) : null,
                    'order'     => $img->order,
                ])->values(),
            ],
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

        // Auto-create banner section
        $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 'A']
        );

        // Auto-create main content section
        $page->sections()->firstOrCreate(
            ['type' => 'calidad_main'],
            ['is_active' => true, 'order' => 'B']
        );

        $page->load(['sections' => fn ($q) => $q->orderBy('order')->with('images')]);
        $sections = $page->sections->map(fn ($s) => $this->expandSection($s, withImages: true))->values();

        $downloads = CalidadDownload::orderBy('order')->get()->map(fn ($d) => [
            'id'          => $d->id,
            'name_es'     => is_array($d->name) ? ($d->name['es'] ?? '') : ($d->name ?? ''),
            'name_en'     => is_array($d->name) ? ($d->name['en'] ?? '') : '',
            'name_pt'     => is_array($d->name) ? ($d->name['pt'] ?? '') : '',
            'logo_url'    => $d->logo ? asset('storage/' . $d->logo) : null,
            'file_url'    => $d->file ? asset('storage/' . $d->file) : null,
            'file_format' => $d->file_format,
            'file_size'   => $d->file_size,
            'order'       => $d->order,
            'is_active'   => $d->is_active,
        ])->values();

        return Inertia::render('Admin/CMS/Calidad', [
            'page'      => $page,
            'sections'  => $sections,
            'downloads' => $downloads,
        ]);
    }

    // ── NOVEDADES PÚBLICAS ──────────────────────────────────────────────

    public function novedades()
    {
        $page = Page::where('slug', 'novedades')
            ->with(['sections' => fn ($q) => $q->orderBy('order')->with('images')])
            ->firstOrCreate(
                ['slug' => 'novedades'],
                ['title' => ['es' => 'Novedades', 'en' => 'News', 'pt' => 'Novidades']]
            );

        $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 'A']
        );

        $page->sections()->firstOrCreate(
            ['type' => 'novedades_intro'],
            [
                'is_active' => true,
                'order' => 'B',
                'title' => [
                    'es' => 'Últimas novedades',
                    'en' => 'Latest news',
                    'pt' => 'Últimas novidades',
                ],
                'content' => [
                    'es' => '<p>Conocé lanzamientos, disponibilidad, incorporaciones y novedades destacadas de Gran Sasso.</p>',
                    'en' => '<p>Explore new releases, availability updates and featured news from Gran Sasso.</p>',
                    'pt' => '<p>Conheça lançamentos, disponibilidade, novidades e destaques da Gran Sasso.</p>',
                ],
            ]
        );

        $page->load(['sections' => fn ($q) => $q->orderBy('order')->with('images')]);
        $sections = $page->sections->map(fn ($s) => $this->expandSection($s, withImages: true))->values();
        $novedades = PublicNovedad::with('galleryImages')->orderByDesc('created_at')
            ->get()
            ->map(fn (PublicNovedad $novedad) => [
                'id' => $novedad->id,
                'title' => $novedad->getNormalizedTranslations('title'),
                'title_display' => $novedad->getLocalizedTitle('es'),
                'category' => $novedad->getNormalizedTranslations('category'),
                'category_display' => $novedad->getLocalizedCategory('es'),
                'excerpt' => $novedad->getNormalizedTranslations('excerpt'),
                'excerpt_display' => $novedad->getLocalizedExcerpt('es'),
                'body' => $novedad->getNormalizedTranslations('body'),
                'body_display' => $novedad->getLocalizedBody('es'),
                'image' => $novedad->image,
                'image_url' => $novedad->image_url,
                'gallery_images' => $novedad->galleryImages->map(fn ($image) => [
                    'id' => $image->id,
                    'image' => $image->image,
                    'image_url' => $image->image_url,
                    'order' => $image->order,
                ])->values(),
                'is_active' => $novedad->is_active,
                'created_at' => optional($novedad->created_at)?->toISOString(),
                'updated_at' => optional($novedad->updated_at)?->toISOString(),
            ])
            ->values();

        return Inertia::render('Admin/CMS/Novedades', [
            'page' => $page,
            'sections' => $sections,
            'novedades' => $novedades,
        ]);
    }

    // ── CONTACTO → info (dirección, teléfono, email, mapa) ───────────────

    public function contacto()
    {
        $page = Page::where('slug', 'contacto')
            ->with(['sections' => fn ($q) => $q->orderBy('order')->with('images')])
            ->firstOrCreate(
                ['slug' => 'contacto'],
                ['title' => ['es' => 'Contacto', 'en' => 'Contact', 'pt' => 'Contato']]
            );

        // Auto-crear secciones necesarias
        $page->sections()->firstOrCreate(
            ['type' => 'banner'],
            ['is_active' => true, 'order' => 0]
        );
        $page->sections()->firstOrCreate(
            ['type' => 'contact_info'],
            ['order' => 1, 'is_active' => true,
             'content' => ['address' => '', 'phones' => [], 'emails' => [], 'map_embed' => '']]
        );

        // Recargar con imágenes
        $page->load(['sections' => fn ($q) => $q->orderBy('order')->with('images')]);

        $infoSection = $page->sections->where('type', 'contact_info')->first();
        $raw = is_array($infoSection?->content) ? $infoSection->content : [];

        // Compatibilidad con formato anterior (phone/email string → arrays)
        $phones = $raw['phones'] ?? (isset($raw['phone']) && $raw['phone'] ? [$raw['phone']] : []);
        $emails = $raw['emails'] ?? (isset($raw['email']) && $raw['email'] ? [$raw['email']] : []);

        $contactInfo = [
            'address'   => $raw['address']   ?? '',
            'phones'    => array_values(array_filter($phones)),
            'emails'    => array_values(array_filter($emails)),
            'map_embed' => $raw['map_embed'] ?? '',
        ];

        $sections = $page->sections->map(function ($s) {
            $arr = $s->toArray();
            if ($s->image) $arr['image_url'] = asset('storage/' . $s->image);
            $arr['images'] = $s->images->map(fn ($img) => array_merge($img->toArray(), [
                'image_url' => asset('storage/' . $img->image),
            ]))->values()->toArray();
            return $arr;
        });

        $messages = \App\Models\ContactMessage::latest()->take(5)->get()->map(fn ($m) => [
            'id'         => $m->id,
            'name'       => $m->name,
            'email'      => $m->email,
            'company'    => $m->company ?? '',
            'is_read'    => $m->is_read,
            'created_at' => $m->created_at->format('d/m/Y H:i'),
        ]);

        return Inertia::render('Admin/CMS/Contacto', [
            'page'           => $page,
            'sections'       => $sections,
            'contactInfo'    => $contactInfo,
            'messages'       => $messages,
            'totalMessages'  => \App\Models\ContactMessage::count(),
            'unreadMessages' => \App\Models\ContactMessage::where('is_read', false)->count(),
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

        // Auto-create main content section
        $page->sections()->firstOrCreate(
            ['type' => 'desarrollos_main'],
            [
                'is_active' => true,
                'order' => 'C',
                'title' => [
                    'es' => 'Desarrollo y fabricación a medida',
                    'en' => 'Custom development and manufacturing',
                    'pt' => 'Desenvolvimento e fabricação sob medida',
                ],
                'content' => [
                    'es' => '<p>Nuestra experiencia en el sector, combinada con tecnología de última generación y un equipo técnico altamente capacitado, nos permite desarrollar y fabricar engranajes y piezas especiales según los requerimientos específicos de cada cliente.</p><p>Trabajamos con precisión y compromiso en cada proyecto, otorgando soluciones confiables y adaptadas a las necesidades de cada aplicación.</p><p>Realizamos el diseño y la producción de repuestos para maquinaria industrial a partir de muestras físicas o planos técnicos de los equipos, garantizando compatibilidad, funcionalidad y altos estándares de calidad.</p><p>Este servicio permite optimizar el rendimiento de los equipos y extender su vida útil, incluso cuando se trata de componentes discontinuados o de difícil acceso.</p><p>Ofrecemos soluciones de nacionalización de repuestos, reduciendo tiempos de espera y costos de importación, así como la fabricación de piezas unitarias o en series, según las necesidades de producción de cada cliente.</p><p>Nuestro enfoque flexible nos permite adaptarnos tanto a proyectos puntuales como a demandas continuas, siempre manteniendo el foco en la precisión, la eficiencia y la confiabilidad.</p>',
                    'en' => '<p>Our experience in the industry, combined with cutting-edge technology and a highly skilled technical team, allows us to develop and manufacture gears and special parts according to the specific requirements of each client.</p><p>We work with precision and commitment on every project, providing reliable solutions adapted to the needs of each application.</p><p>We carry out the design and production of spare parts for industrial machinery based on physical samples or technical drawings, ensuring compatibility, functionality and high quality standards.</p><p>This service optimises equipment performance and extends its useful life, even for discontinued or hard-to-find components.</p><p>We offer spare parts localisation solutions, reducing lead times and import costs, as well as single-unit or series production according to each client\'s needs.</p><p>Our flexible approach allows us to adapt to both one-off projects and ongoing demands, always keeping the focus on precision, efficiency and reliability.</p>',
                    'pt' => '<p>Nossa experiência no setor, combinada com tecnologia de última geração e uma equipe técnica altamente capacitada, nos permite desenvolver e fabricar engrenagens e peças especiais de acordo com os requisitos específicos de cada cliente.</p><p>Trabalhamos com precisão e compromisso em cada projeto, fornecendo soluções confiáveis e adaptadas às necessidades de cada aplicação.</p><p>Realizamos o design e a produção de peças de reposição para maquinário industrial a partir de amostras físicas ou desenhos técnicos dos equipamentos, garantindo compatibilidade, funcionalidade e altos padrões de qualidade.</p><p>Este serviço otimiza o desempenho dos equipamentos e estende sua vida útil, mesmo para componentes descontinuados ou de difícil acesso.</p><p>Oferecemos soluções de nacionalização de peças de reposição, reduzindo prazos de espera e custos de importação, bem como a fabricação de peças unitárias ou em série, de acordo com as necessidades de produção de cada cliente.</p><p>Nossa abordagem flexível nos permite adaptar tanto a projetos pontuais quanto a demandas contínuas, sempre mantendo o foco na precisão, eficiência e confiabilidade.</p>',
                ],
            ]
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
            'autoplay_seconds' => 'nullable|integer|min:1|max:30',
            'order'          => 'nullable|string|max:20',
            'is_active'      => 'boolean',
            'image'          => 'nullable|image|max:4096',
        ]);

        $data = $this->sanitizePlainTextFields($data, ['title', 'subtitle', 'button_text']);
        $data = $this->sanitizeDataFields($data, ['content']);
        $buttonLink = $this->sanitizeUrl($data['button_link'] ?? null, allowRelative: true);

        $imagePath = null;
        $isGallerySection = in_array($data['type'], ['about_main', 'calidad_main'], true);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('pages', 'public');
        }

        $section = $page->sections()->create([
            'type'        => $data['type'],
            'title'       => ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null],
            'subtitle'    => ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null],
            'content'     => ['es' => $data['content_es'] ?? null, 'en' => $data['content_en'] ?? null, 'pt' => $data['content_pt'] ?? null],
            'button_text' => ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null],
            'button_link' => $buttonLink,
            'autoplay_seconds' => $data['autoplay_seconds'] ?? 3,
            'order'       => strtoupper(trim($data['order'] ?? 'A')),
            'is_active'   => $data['is_active'] ?? true,
            'image'       => $isGallerySection ? null : $imagePath,
        ]);

        if ($isGallerySection && $imagePath) {
            $section->images()->create($this->makeSectionImagePayload(
                imagePath: $imagePath,
                order: 'A',
                kind: 'gallery',
            ));
        }

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
            'autoplay_seconds' => 'nullable|integer|min:1|max:30',
            'order'          => 'nullable|string|max:20',
            'is_active'      => 'boolean',
            'image'          => 'nullable|image|max:4096',
        ]);

        $data = $this->sanitizePlainTextFields($data, ['title', 'subtitle', 'button_text']);
        $data = $this->sanitizeDataFields($data, ['content']);
        $buttonLink = array_key_exists('button_link', $data)
            ? $this->sanitizeUrl($data['button_link'], allowRelative: true)
            : $section->button_link;

        $imagePath = $section->image;
        $newGalleryImagePath = null;
        $isGallerySection = in_array($section->type, ['about_main', 'calidad_main'], true);

        if ($request->hasFile('image')) {
            $storedPath = $request->file('image')->store('pages', 'public');

            if ($isGallerySection) {
                if ($imagePath) {
                    Storage::disk('public')->delete($imagePath);
                    $imagePath = null;
                }
                $newGalleryImagePath = $storedPath;
            } else {
                if ($imagePath) Storage::disk('public')->delete($imagePath);
                $imagePath = $storedPath;
            }
        }

        $existing = fn ($field) => is_array($section->$field)
            ? $section->$field
            : ['es' => null, 'en' => null, 'pt' => null];

        $section->update([
            'title'       => array_merge($existing('title'),       ['es' => $data['title_es'] ?? null, 'en' => $data['title_en'] ?? null, 'pt' => $data['title_pt'] ?? null]),
            'subtitle'    => array_merge($existing('subtitle'),    ['es' => $data['subtitle_es'] ?? null, 'en' => $data['subtitle_en'] ?? null, 'pt' => $data['subtitle_pt'] ?? null]),
            'content'     => array_merge($existing('content'),     ['es' => $data['content_es'] ?? null, 'en' => $data['content_en'] ?? null, 'pt' => $data['content_pt'] ?? null]),
            'button_text' => array_merge($existing('button_text'), ['es' => $data['button_text_es'] ?? null, 'en' => $data['button_text_en'] ?? null, 'pt' => $data['button_text_pt'] ?? null]),
            'button_link' => $buttonLink,
            'autoplay_seconds' => $data['autoplay_seconds'] ?? $section->autoplay_seconds ?? 3,
            'order'       => isset($data['order']) ? strtoupper(trim($data['order'])) : $section->order,
            'is_active'   => $data['is_active']   ?? $section->is_active,
            'image'       => $imagePath,
        ]);

        if ($newGalleryImagePath) {
            $existingOrders = $section->images()
                ->where('kind', 'gallery')
                ->pluck('order')
                ->toArray();

            $section->images()->create([
                ...$this->makeSectionImagePayload(
                    imagePath: $newGalleryImagePath,
                    order: $this->nextAlphaOrder($existingOrders),
                    kind: 'gallery',
                ),
            ]);
        }

        return back()->with('success', 'Sección actualizada.');
    }

    // ── Agregar imagen a una sección (banner, galería, etc.) ─────────────

    public function addSectionImage(Request $request, PageSection $section)
    {
        if ($section->type === 'home_gallery' && $section->images()->count() >= 3) {
            return back()->withErrors([
                'image' => 'Esta sección admite hasta 3 imágenes.',
            ]);
        }

        $request->validate([
            'image'     => 'nullable|image|max:5120',
            'images'    => 'nullable|array',
            'images.*'  => 'nullable|image|max:5120',
            'video_url' => 'nullable|string|max:500',
            'kind'      => 'nullable|string|in:default,gallery,badge',
            'order'     => 'nullable|string|max:20',
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('images')) {
            $uploadedFiles = array_values(array_filter($request->file('images', [])));
        } elseif ($request->hasFile('image')) {
            $uploadedFiles = [$request->file('image')];
        }

        if (empty($uploadedFiles) && ! $request->filled('video_url')) {
            return back()->withErrors(['image' => 'Se requiere una imagen o URL de video.']);
        }

        if ($section->type === 'home_gallery' && ($section->images()->count() + count($uploadedFiles)) > 3) {
            return back()->withErrors([
                'images' => 'Esta sección admite hasta 3 imágenes.',
            ]);
        }

        $currentOrder = $request->filled('order')
            ? strtoupper(trim($request->input('order')))
            : $this->nextAlphaOrder($section->images()->pluck('order')->toArray());

        if (! empty($uploadedFiles)) {
            foreach ($uploadedFiles as $index => $uploadedFile) {
                $path = $uploadedFile->store('pages/banner', 'public');

                $section->images()->create($this->makeSectionImagePayload(
                    imagePath: $path,
                    videoUrl: null,
                    order: $currentOrder,
                    kind: $request->input('kind', 'default'),
                ));

                if ($index < count($uploadedFiles) - 1) {
                    $currentOrder = $this->nextAlphaOrder(array_merge(
                        $section->images()->pluck('order')->toArray(),
                        [$currentOrder]
                    ));
                }
            }

            $count = count($uploadedFiles);
            return back()->with('success', $count === 1 ? 'Imagen agregada.' : "Se agregaron {$count} imágenes.");
        }

        $section->images()->create($this->makeSectionImagePayload(
            imagePath: null,
            videoUrl: $request->filled('video_url') ? $request->input('video_url') : null,
            order: $currentOrder,
            kind: $request->input('kind', 'default'),
        ));

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

    private function makeSectionImagePayload(?string $imagePath, string $order, string $kind = 'default', ?string $videoUrl = null): array
    {
        $payload = [
            'image' => $imagePath,
            'video_url' => $videoUrl,
            'order' => $order,
        ];

        if (Schema::hasColumn('page_section_images', 'kind')) {
            $payload['kind'] = $kind;
        }

        return $payload;
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
        $request->validate([
            'address'   => 'nullable|string|max:500',
            'phones'    => 'nullable|array',
            'phones.*'  => 'nullable|string|max:100',
            'emails'    => 'nullable|array',
            'emails.*'  => 'nullable|string|max:200',
            'map_embed' => 'nullable|string',
        ]);

        $page = Page::where('slug', 'contacto')->firstOrFail();
        $section = $page->sections()->firstOrCreate(['type' => 'contact_info'], ['order' => 1, 'is_active' => true]);

        $section->update(['content' => [
            'address'   => $request->input('address', ''),
            'phones'    => array_values(array_filter($request->input('phones', []))),
            'emails'    => array_values(array_filter($request->input('emails', []))),
            'map_embed' => $this->sanitizeMapEmbed($request->input('map_embed', '')),
        ]]);

        return back()->with('success', 'Datos de contacto actualizados.');
    }

    // ── Helper: expandir sección (JSON fields → es/en/pt separados) ──────

    private function expandSection(PageSection $section, bool $withImages = false): array
    {
        $richFields = ['content'];
        $get = function ($field, $lang) use ($section, $richFields) {
            $value = is_array($section->$field)
                ? ($section->$field[$lang] ?? '')
                : ($lang === 'es' ? ($section->$field ?? '') : '');

            return in_array($field, $richFields, true)
                ? $value
                : $this->sanitizePlainText($value);
        };

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
            'autoplay_seconds' => $section->autoplay_seconds ?? 3,
            'order'          => $section->order,
            'is_active'      => $section->is_active,
            'image_url'      => $section->image ? asset('storage/' . $section->image) : null,
        ];

        if ($withImages) {
            $result['images'] = $section->images->sortBy('order')->map(fn ($img) => [
                'id'        => $img->id,
                'image_url' => $img->image ? asset('storage/' . $img->image) : null,
                'kind'      => $img->kind ?? 'default',
                'order'     => $img->order,
            ])->values();
        }

        return $result;
    }

    // ── Helper: aplanar sección (JSON fields → ES por defecto) ───────────

    private function flattenSection(PageSection $section): array
    {
        $str = function ($field) use ($section) {
            $value = is_array($section->$field)
                ? ($section->$field['es'] ?? '')
                : ($section->$field ?? '');

            return $field === 'content'
                ? $value
                : $this->sanitizePlainText($value);
        };

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
