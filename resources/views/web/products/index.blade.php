@extends('web.layouts.app')
@section('title', __('ui.products.title'))

@section('content')

{{-- ══ BANNER ══════════════════════════════════════════════════════════════ --}}
<section class="products-banner">

    {{-- Fondo: dark navy por defecto. El CMS puede agregar una imagen real después. --}}
    <div class="products-banner__bg"></div>
    <div class="products-banner__overlay-dark"></div>
    <div class="products-banner__overlay"></div>

    <div class="products-banner__inner">

        {{-- Breadcrumb: 11px bajo el header --}}
        <div class="products-banner__topbar">
            <nav class="products-banner__breadcrumb"
                 aria-label="{{ __('ui.products.bc_aria') }}">
                <a href="{{ route('home') }}" class="products-banner__bc-home">
                    {{ __('ui.breadcrumb.home') }}
                </a>
                <svg class="products-banner__bc-sep"
                     xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 16 16" fill="none" aria-hidden="true">
                    <path d="M6 3L11 8L6 13" stroke="white" stroke-width="1.5"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="products-banner__bc-current">
                    {{ __('ui.breadcrumb.products') }}
                </span>
            </nav>
        </div>

        {{-- Título --}}
        <div class="products-banner__bottom">
            <h1 class="products-banner__title">{{ __('ui.products.title') }}</h1>
        </div>

    </div>
</section>

{{-- ══ BUSCADOR (se superpone al banner con margin-top negativo) ════════════ --}}
@include('web.home.sections.hero-search')

{{-- ══ GRID DE MARCAS ══════════════════════════════════════════════════════ --}}
<section class="products-index">
    <div class="container">

        <div class="products-index__grid">

            @forelse($brands as $brand)

            <a href="{{ route('products.brand', $brand->slug) }}" class="brand-card">

                <div class="brand-card__logo">
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}"
                             alt="{{ $brand->getName() }}"
                             loading="lazy">
                    @else
                        <span class="brand-card__name-fallback">{{ $brand->getName() }}</span>
                    @endif
                </div>

                <span class="brand-card__divider"></span>

                <div class="brand-card__footer">
                    <span class="brand-card__compatible">{{ __('ui.products.compatible_with') }}</span>
                    <strong class="brand-card__brand">{{ strtoupper($brand->getName()) }}</strong>
                </div>

            </a>

            @empty
            <p class="products-empty">{{ __('ui.products.no_brands') }}</p>
            @endforelse

        </div>

    </div>
</section>

@endsection
