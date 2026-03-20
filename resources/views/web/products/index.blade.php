@extends('web.layouts.app')
@section('title', __('ui.products.title'))

@section('content')

@php
$hasSlides = $bannerSlides->count() > 0;
$n         = $bannerSlides->count();
@endphp

{{-- ═════════════════════════════════════
BANNER (Figma correcto)
═════════════════════════════════════ --}}

<div class="products-banner" data-slides="{{ $n }}">

{{-- SLIDES --}}
@if($hasSlides)
    @foreach($bannerSlides as $i => $slide)
    @php
        $ytId = null;
        if ($slide->video_url) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $slide->video_url, $ytMatch);
            $ytId = $ytMatch[1] ?? null;
        }
        $embedUrl = $ytId
            ? 'https://www.youtube.com/embed/' . $ytId . '?autoplay=1&mute=1&controls=0&loop=1&playlist=' . $ytId . '&playsinline=1&rel=0'
            : null;
    @endphp

    <div class="products-banner__slide {{ $i === 0 ? 'active' : '' }}"
         data-index="{{ $i }}"
         @if(!$ytId && $slide->image)
             style="background-image: url('{{ asset('storage/' . $slide->image) }}')"
         @endif>

        @if($embedUrl)
        <iframe
            class="products-banner__video"
            src="{{ $i === 0 ? $embedUrl : '' }}"
            data-src="{{ $embedUrl }}"
            title="Video slide {{ $i + 1 }}"
            allow="autoplay; encrypted-media"
            allowfullscreen
            frameborder="0"></iframe>
        @endif

    </div>
    @endforeach
@else
    <div class="products-banner__slide active" style="background-color:#0D2038"></div>
@endif

{{-- OVERLAYS --}}
<div class="products-banner__overlay-dark"></div>
<div class="products-banner__overlay"></div>

{{-- INNER --}}
<div class="products-banner__inner">

    {{-- BREADCRUMB --}}
    <div class="products-banner__topbar">
        <nav class="products-banner__breadcrumb" aria-label="{{ __('ui.products.bc_aria') }}">
            <a href="{{ route('home') }}" class="products-banner__bc-home">{{ __('ui.breadcrumb.home') }}</a>
            <svg class="products-banner__bc-sep" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M4.5 2.5L7.5 6L4.5 9.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="products-banner__bc-current">{{ __('ui.breadcrumb.products') }}</span>
        </nav>
    </div>

    {{-- BOTTOM --}}
    <div class="products-banner__bottom">

        <h1 class="products-banner__title">
            {{ __('ui.products.title') }}
        </h1>

        {{-- DOTS --}}
        @if($n >= 2)
        <div class="products-banner__dots">
            @foreach($bannerSlides as $i => $slide)
            <button
                class="products-banner__dot {{ $i === 0 ? 'active' : '' }}"
                data-index="{{ $i }}"
                aria-label="Ir al slide {{ $i + 1 }}"
            ></button>
            @endforeach
        </div>
        @endif

    </div>
</div>

</div>

{{-- ═════════════════════════════════════
BUSCADOR (solapado sobre banner)
═════════════════════════════════════ --}}

<div class="products-search">
    <div class="products-search__box">

        <form class="products-search__form"
              action="{{ route('products.search') }}"
              method="GET"
              id="productsSearchForm">

            {{-- MARCA --}}
            <div class="products-search__field">
                <label class="products-search__label" for="ps-marca">{{ __('ui.search.brand') }}</label>
                <div class="products-search__select-wrap">
                    <select name="marca"
                            id="ps-marca"
                            class="products-search__select">
                        <option value="">{{ __('ui.search.select_brand') }}</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->slug }}"
                            {{ request('marca') === $brand->slug ? 'selected' : '' }}>
                            {{ $brand->getName() }}
                        </option>
                        @endforeach
                    </select>
                    <svg class="products-search__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <g opacity="0.6">
                            <path d="M5 7.5L10 12.5L15 7.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                    </svg>
                </div>
            </div>

            {{-- MODELO --}}
            <div class="products-search__field">
                <label class="products-search__label" for="ps-modelo">{{ __('ui.search.model') }}</label>
                <div class="products-search__select-wrap">
                    <select name="modelo"
                            id="ps-modelo"
                            class="products-search__select products-search__select--modelo">
                        <option value="">{{ __('ui.search.select_model') }}</option>
                    </select>
                    <svg class="products-search__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <g opacity="0.6">
                            <path d="M5 7.5L10 12.5L15 7.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                    </svg>
                </div>
            </div>

            {{-- CÓDIGO --}}
            <div class="products-search__field products-search__field--codigo">
                <label class="products-search__label" for="ps-codigo">{{ __('ui.search.code') }}</label>
                <input type="text"
                       name="codigo"
                       id="ps-codigo"
                       class="products-search__input"
                       placeholder="{{ __('ui.search.code_placeholder') }}"
                       value="{{ request('codigo') }}">
            </div>

            {{-- BUSCAR --}}
            <div class="products-search__btn-wrap">
                <button type="submit" class="products-search__btn">
                    {{ __('ui.search.button') }}
                </button>
            </div>

        </form>

    </div>
</div>

@push('scripts')
<script>
(function () {

    /* ── Placeholder visual: opacity 0.6 cuando no hay valor seleccionado ── */
    document.querySelectorAll('.products-search__select').forEach(function (sel) {
        function syncPlaceholder() {
            sel.classList.toggle('is-placeholder', sel.value === '');
        }
        syncPlaceholder();
        sel.addEventListener('change', syncPlaceholder);
    });

    /* ── AJAX: cargar modelos según marca ── */
    var marcaSelect  = document.getElementById('ps-marca');
    var modeloSelect = document.getElementById('ps-modelo');

    if (!marcaSelect || !modeloSelect) return;

    function loadModels(brandSlug, selectedModel) {
        modeloSelect.innerHTML = '<option value="">{{ __('ui.search.select_model') }}</option>';
        modeloSelect.disabled = !brandSlug;
        if (!brandSlug) return;

        fetch('/api/gearboxes?brand=' + encodeURIComponent(brandSlug))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                (data.gearboxes || data).forEach(function (g) {
                    var opt = document.createElement('option');
                    opt.value = g.slug;
                    opt.textContent = g.name;
                    if (g.slug === selectedModel) opt.selected = true;
                    modeloSelect.appendChild(opt);
                });
            })
            .catch(function () {});
    }

    var initialBrand = marcaSelect.value;
    var initialModel = '{{ request('modelo') }}';
    if (initialBrand) loadModels(initialBrand, initialModel);

    marcaSelect.addEventListener('change', function () {
        loadModels(this.value, '');
    });
}());
</script>
@endpush

{{-- ═════════════════════════════════════
CONTENIDO
═════════════════════════════════════ --}}

<section class="products-index">
    <div class="container">

    {{-- GRID --}}
    <div class="products-index__grid">
        @foreach($brands as $brand)
        <a href="{{ route('products.brand', $brand->slug) }}" class="brand-card">

            <div class="brand-card__logo">
                @if($brand->logo)
                    <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->getName() }}">
                @else
                    <span class="brand-card__name-fallback">
                        {{ $brand->getName() }}
                    </span>
                @endif
            </div>

            <span class="brand-card__divider"></span>

            <div class="brand-card__footer">
                <span class="brand-card__compatible">
                    {{ __('ui.products.compatible_with') }}
                </span>
                <strong class="brand-card__brand">
                    {{ strtoupper($brand->getName()) }}
                </strong>
            </div>

        </a>
        @endforeach
    </div>

</div>

</section>

@endsection
