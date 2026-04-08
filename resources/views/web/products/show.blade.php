@extends('web.layouts.app')

@php
    $oemCode               = $productView['primary_oem_code'];
    $productName           = ! empty($productView['display_name']) ? $productView['display_name'] : null;
    $rawDesc               = $productView['display_description'];
    $productDesc           = $rawDesc ?: null;
    $figure                = $productView['figure'];
    $codeGs                = $productView['code_gs'];
    $refOriginal           = $productView['ref_original'];
    $refsAdicionales       = collect($productView['refs_adicionales']);
    $variants              = collect($productView['variant_labels']);
    $applications          = collect($productView['applications']);
    $altOemCodes           = collect($productView['alt_oem_codes']);
    $notes                 = collect($productView['notes']);
    $catalogRows           = collect($productView['catalog_rows']);
    $catalogVariantColumns = collect($productView['catalog_variant_columns']);
    $gearboxCatalogMeta    = is_array($gearbox->catalog_meta ?? null) ? $gearbox->catalog_meta : [];
    $catalogHeaderNotes    = collect($gearboxCatalogMeta['application_secondary'] ?? [])
        ->map(fn ($value) => preg_replace('/\s+/u', ' ', trim((string) $value)) ?: null)
        ->filter()
        ->unique(fn ($value) => mb_strtolower($value))
        ->values();
    $galleryImages         = $product->images
        ->filter(fn ($img) => !empty($img->url))
        ->values();
    $fallbackImageSrc      = $product->resolved_image_url;

    if ($productName && $productDesc && mb_strtolower($productName) === mb_strtolower($productDesc)) {
        $productDesc = null;
    }
@endphp

@section('title', $oemCode . ' — ' . ($productName ?? $brand->getName()))

@section('content')

<section class="product-detail">
    <div class="container">

        {{-- ══ BREADCRUMB ════════════════════════════════════════════════ --}}
        <nav class="breadcrumb" aria-label="{{ __('ui.products.bc_aria') }}">
            <a href="{{ route('home') }}">{{ __('ui.breadcrumb.home') }}</a>
            <span>›</span>
            <a href="{{ route('products.index') }}">{{ __('ui.breadcrumb.products') }}</a>
            <span>›</span>
            <a href="{{ route('products.brand', $brand->slug) }}">{{ $brand->getName() }}</a>
            <span>›</span>
            <a href="{{ route('products.gearbox', [$brand->slug, $gearbox->slug]) }}">{{ $gearbox->name }}</a>
            <span>›</span>
            <span>{{ $oemCode }}</span>
        </nav>

        {{-- ══ LAYOUT: GALERÍA + INFO ════════════════════════════════════ --}}
        <div class="product-detail__layout">

            {{-- GALERÍA --}}
            <div class="product-detail__gallery">

                {{-- Thumbnails --}}
                <div class="product-detail__thumbs">
                    @forelse($galleryImages as $img)
                    <div class="product-detail__thumb {{ $loop->first ? 'active' : '' }}"
                         data-src="{{ $img->url }}"
                         role="button"
                         tabindex="0"
                         aria-label="Imagen {{ $loop->iteration }}">
                        <img src="{{ $img->url }}"
                             alt="{{ $oemCode }} — imagen {{ $loop->iteration }}">
                    </div>
                    @empty
                    @if($fallbackImageSrc)
                    <div class="product-detail__thumb active"
                         data-src="{{ $fallbackImageSrc }}">
                        <img src="{{ $fallbackImageSrc }}"
                             alt="{{ $oemCode }}">
                    </div>
                    @else
                    <div class="product-detail__thumb active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42"
                             viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                             stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                    @endif
                    @endforelse
                </div>

                {{-- Imagen principal --}}
                <div class="product-detail__main-img" id="productMainDisplay">
                    @php
                        $mainImg = $galleryImages->first();
                        $mainSrc = $mainImg?->url ?? $fallbackImageSrc;
                    @endphp
                    @if($mainSrc)
                    <img src="{{ $mainSrc }}"
                         alt="{{ $oemCode }}"
                         id="productMainImg">
                    @else
                    <div class="product-detail__main-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="72" height="72"
                             viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                             stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                    @endif
                    <button class="product-detail__zoom"
                            aria-label="{{ __('ui.product_detail.zoom') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12H19M12 5V19" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

            </div>

            {{-- INFO --}}
            <div class="product-detail__info">

                {{-- Marca --}}
                <span class="product-detail__brand">{{ strtoupper($brand->getName()) }}</span>

                {{-- OEM code como título principal --}}
                <h1 class="product-detail__code">{{ $oemCode }}</h1>

                {{-- Divisor 1: directo bajo el código OEM --}}
                <hr class="product-detail__sep">

                @if($codeGs || $figure)
                <div class="product-detail__headline-meta">
                    @if($codeGs)
                    <span class="product-detail__headline-pill"># GSS {{ $codeGs }}</span>
                    @endif
                    @if($figure)
                    <span class="product-detail__headline-pill">FIG. {{ $figure }}</span>
                    @endif
                </div>
                @endif

                {{-- Nombre del producto --}}
                @if($productName)
                <p class="product-detail__name" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-style:normal;font-size:18px;font-weight:400;line-height:25px;margin-bottom:40px;">{!! nl2br(e($productName)) !!}</p>
                @endif

                {{-- Descripción --}}
                @if($productDesc)
                <p class="product-detail__desc" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-style:normal;font-size:18px;font-weight:400;line-height:25px;margin-bottom:40px;">{!! nl2br(e($productDesc)) !!}</p>
                @endif

                {{-- ── Tabla de datos ─────────────────────────────────── --}}
                <table class="product-detail__refs">

                    {{-- Código GS --}}
                    @if($codeGs)
                    <tr>
                        <td>{{ __('ui.product_detail.gs_code') }}</td>
                        <td>{{ $codeGs }}</td>
                    </tr>
                    @endif

                    {{-- Figura --}}
                    @if($figure)
                    <tr>
                        <td>{{ __('ui.product_detail.figure') }}</td>
                        <td>{{ $figure }}</td>
                    </tr>
                    @endif

                    {{-- Modelo de caja --}}
                    <tr>
                        <td>{{ __('ui.product_detail.gearbox_model') }}</td>
                        <td>{{ $gearbox->name }}</td>
                    </tr>

                    {{-- Vehículos compatibles de la caja --}}
                    @if($gearbox->compatible_vehicles)
                    <tr>
                        <td>{{ __('ui.product_detail.compatible_vehicles') }}</td>
                        <td>{{ $gearbox->compatible_vehicles }}</td>
                    </tr>
                    @endif

                    @if($catalogHeaderNotes->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.catalog_data') }}</td>
                        <td>
                            @foreach($catalogHeaderNotes as $catalogNote)
                            {{ $catalogNote }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Campos extra de la caja --}}
                    @if($gearbox->extra_field_1)
                    <tr>
                        <td>{{ __('ui.product_detail.additional_info') }}</td>
                        <td>{{ $gearbox->extra_field_1 }}</td>
                    </tr>
                    @endif

                    @if($gearbox->extra_field_2)
                    <tr>
                        <td></td>
                        <td>{{ $gearbox->extra_field_2 }}</td>
                    </tr>
                    @endif

                    {{-- Variantes compatibles --}}
                    @if($variants->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.compatible_variants') }}</td>
                        <td>{{ $variants->implode(' / ') }}</td>
                    </tr>
                    @endif

                    {{-- Referencia Original --}}
                    @if($refOriginal)
                    <tr>
                        <td>{{ __('ui.product_detail.original_ref') }}</td>
                        <td>{{ $refOriginal }}</td>
                    </tr>
                    @endif

                    {{-- Referencias Adicionales --}}
                    @if($refsAdicionales->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.additional_ref') }}</td>
                        <td>{{ $refsAdicionales->implode(' ; ') }}</td>
                    </tr>
                    @endif

                    {{-- Códigos OEM alternativos --}}
                    @if($altOemCodes->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.alt_oem') }}</td>
                        <td>{{ $altOemCodes->implode(' / ') }}</td>
                    </tr>
                    @endif

                    {{-- Aplicaciones de vehículo --}}
                    @if($applications->isNotEmpty() && $catalogRows->isEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.application') }}</td>
                        <td>
                            @foreach($applications as $app)
                            {{ $app }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </td>
                    </tr>
                    @endif

                    {{-- Notas --}}
                    @if($notes->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.notes') }}</td>
                        <td>
                            @foreach($notes as $note)
                            {{ $note }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </td>
                    </tr>
                    @endif

                </table>

                {{-- ── Acciones ──────────────────────────────────────── --}}
                <div class="product-detail__actions">

                    <a href="{{ route('products.pdf', [$brand->slug, $gearbox->slug, $product->slug]) }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn--outline">
                        {{ __('ui.product_detail.technical_sheet') }}
                    </a>

                    <a href="{{ url('/contacto') }}?producto={{ $oemCode }}"
                       class="btn btn--primary">
                        {{ __('ui.product_detail.inquire') }}
                    </a>

                </div>

            </div>
        </div>

        @if($catalogRows->isNotEmpty())
        <section class="product-detail__catalog">
            <div class="product-detail__catalog-head">
                <span class="product-detail__catalog-eyebrow">{{ __('ui.product_detail.catalog_eyebrow') }}</span>
                <h2 class="product-detail__catalog-title">{{ __('ui.product_detail.catalog_title') }}</h2>
                <p class="product-detail__catalog-copy">{{ __('ui.product_detail.catalog_copy') }}</p>
            </div>

            <div class="product-detail__catalog-wrap">
                <table class="product-detail__catalog-table">
                    <thead>
                        <tr>
                            <th>Fig.</th>
                            <th># GSS</th>
                            <th># OEM</th>
                            <th>{{ __('ui.product_detail.description') }}</th>
                            <th>{{ __('ui.product_detail.application') }}</th>
                            @if($catalogVariantColumns->isNotEmpty())
                                @foreach($catalogVariantColumns as $variant)
                                <th>{{ $variant }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($catalogRows as $row)
                        <tr>
                            <td>{{ $row['figure'] ?: '—' }}</td>
                            <td>{{ $row['code_gs'] ?: '—' }}</td>
                            <td>{{ $row['oem_code'] ?: '—' }}</td>
                            <td>
                                <div class="product-detail__catalog-desc">
                                    {!! $row['description'] ? nl2br(e($row['description'])) : '—' !!}
                                </div>
                                @if(!empty($row['reference_original']))
                                <div class="product-detail__catalog-note">{{ __('ui.product_detail.catalog_original_ref') }}: {{ $row['reference_original'] }}</div>
                                @endif
                                @if(!empty($row['additional_references']))
                                <div class="product-detail__catalog-note">{{ __('ui.product_detail.catalog_additional_refs') }}: {{ implode(' / ', $row['additional_references']) }}</div>
                                @endif
                                @if(!empty($row['notes']))
                                <div class="product-detail__catalog-note">{{ __('ui.product_detail.notes') }}: {{ $row['notes'] }}</div>
                                @endif
                            </td>
                            <td>{{ !empty($row['applications']) ? implode(' / ', $row['applications']) : '—' }}</td>
                            @if($catalogVariantColumns->isNotEmpty())
                                @foreach($catalogVariantColumns as $variant)
                                <td class="product-detail__catalog-check">
                                    {{ in_array($variant, $row['variants'], true) ? 'X' : '—' }}
                                </td>
                                @endforeach
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        {{-- ══ PRODUCTOS RELACIONADOS ═════════════════════════════════════ --}}
        @if($related->count())
        <div class="product-related">
            <h2 class="product-related__title">{{ __('ui.product_detail.related') }}</h2>
            <div class="products-grid">
                @foreach($related as $rel)
                @php
                    $relCard        = $rel->catalog_card ?? [];
                    $relTitle       = $relCard['title_code'] ?? ($rel->code_gs ?? '—');
                    $relOem         = $relCard['primary_oem_code'] ?? '—';
                    $relDescription = ! empty($relCard['description'])
                        ? ucwords(mb_strtolower($relCard['description']))
                        : null;
                @endphp
                <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $rel->slug]) }}"
                   class="rel-card">

                    <div class="rel-card__img">
                        @if($rel->resolved_image_url)
                            <img src="{{ $rel->resolved_image_url }}"
                                 alt="{{ $relOem }}" loading="lazy">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                 viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                                 stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        @endif
                        <div class="rel-card__overlay"></div>
                        <span class="rel-card__plus">+</span>
                    </div>

                    <span class="rel-card__divider"></span>

                    <div class="rel-card__body">
                        <span class="rel-card__brand">{{ strtoupper($brand->getName()) }}</span>
                        <h3 class="rel-card__name">{{ $relTitle }}</h3>
                        @if($relDescription)
                        <p class="rel-card__subtitle">{{ $relDescription }}</p>
                        @endif
                    </div>

                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

{{-- GALLERY INTERACTION ────────────────────────────────────────────────── --}}
@push('scripts')
<script>
(function () {
    var thumbs   = document.querySelectorAll('.product-detail__thumb');
    var mainImg  = document.getElementById('productMainImg');

    if (!thumbs.length || !mainImg) return;

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            thumbs.forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            mainImg.src = this.dataset.src;
        });
        // Soporte teclado
        thumb.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
        });
    });
}());
</script>
@endpush

@endsection
