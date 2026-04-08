@extends('web.layouts.app')
@section('title', $brand->getName() . ' — ' . __('ui.products.title'))

@section('content')

<section class="products-body">
    <div class="container">

        {{-- ══ BREADCRUMB ══════════════════════════════════════════════════ --}}
        <nav class="products-bc" aria-label="{{ __('ui.products.bc_aria') }}">
            <a href="{{ route('home') }}" class="products-bc__link">
                {{ __('ui.breadcrumb.home') }}
            </a>
            <svg class="products-bc__sep" xmlns="http://www.w3.org/2000/svg"
                 width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M6 3L11 8L6 13" stroke="#9CA3AF" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <a href="{{ route('products.index') }}" class="products-bc__link">
                {{ __('ui.breadcrumb.products') }}
            </a>
            <svg class="products-bc__sep" xmlns="http://www.w3.org/2000/svg"
                 width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M6 3L11 8L6 13" stroke="#9CA3AF" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="products-bc__current">{{ $brand->getName() }}</span>
        </nav>

        {{-- ══ LAYOUT: SIDEBAR + MAIN ══════════════════════════════════════ --}}
        <div class="products-layout">

            {{-- SIDEBAR — todas las marcas, activa en negrita --}}
            <aside class="products-sidebar">
                @foreach($brands as $b)
                <a href="{{ route('products.brand', $b->slug) }}"
                   class="products-sidebar__item {{ $b->id === $brand->id ? 'active' : '' }}">
                    {{ strtoupper($b->getName()) }}
                </a>
                @endforeach
            </aside>

            {{-- MAIN — cajas de cambio de la marca activa --}}
            <div class="products-main">

                @if($gearboxes->isEmpty())
                <p class="products-empty">{{ __('ui.products.no_models') }}</p>
                @else
                <div class="gearboxes-grid">
                    @foreach($gearboxes as $gearbox)
                    <a href="{{ route('products.gearbox', [$brand->slug, $gearbox->slug]) }}"
                       class="gearbox-card">

                        {{-- Imagen --}}
                        <div class="gearbox-card__img">
                            @if($gearbox->resolved_image_url)
                                <img src="{{ $gearbox->resolved_image_url }}"
                                     alt="{{ $gearbox->name }}"
                                     loading="lazy">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"
                                     viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                                     stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            @endif
                            <div class="gearbox-card__overlay"></div>
                            <span class="gearbox-card__plus">+</span>
                        </div>

                        <div class="gearbox-card__body">
                            <span class="gearbox-card__brand">{{ strtoupper($brand->getName()) }}</span>
                            <span class="gearbox-card__name">{{ $gearbox->name }}</span>
                        </div>

                    </a>
                    @endforeach
                </div>
                @endif

            </div>
        </div>

    </div>
</section>

@endsection
