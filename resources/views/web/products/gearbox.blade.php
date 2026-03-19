@extends('web.layouts.app')
@section('title', $gearbox->name . ' — ' . $brand->getName())

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
            <a href="{{ route('products.brand', $brand->slug) }}" class="products-bc__link">
                {{ $brand->getName() }}
            </a>
            <svg class="products-bc__sep" xmlns="http://www.w3.org/2000/svg"
                 width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M6 3L11 8L6 13" stroke="#9CA3AF" stroke-width="1.5"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="products-bc__current">{{ $gearbox->name }}</span>
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

            {{-- MAIN — productos de esta caja de cambios --}}
            <div class="products-main">

                @if($products->isEmpty())
                <p class="products-empty">{{ __('ui.products.no_products') }}</p>
                @else
                <div class="products-grid">
                    @foreach($products as $product)

                    {{-- El OEM code viene del primer item (accessor backward-compat) --}}
                    @php
                        $primaryItem = $product->items->first();
                        $oemCode     = $primaryItem?->oem_code ?? $product->code_gs ?? '—';
                        $productName = $product->getName();
                    @endphp

                    <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $product->id]) }}"
                       class="product-card">

                        {{-- IMAGEN --}}
                        <div class="product-card__img-wrap">
                            @if($product->primaryImage)
                                <img src="{{ asset('storage/' . $product->primaryImage->path) }}"
                                     alt="{{ $oemCode }}"
                                     loading="lazy">
                            @elseif($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $oemCode }}"
                                     loading="lazy">
                            @else
                                {{-- Placeholder SVG si no hay imagen --}}
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"
                                     viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                                     stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            @endif
                            <div class="product-card__overlay">
                                <span class="product-card__plus">+</span>
                            </div>
                        </div>

                        {{-- INFO --}}
                        <div class="product-card__body">
                            <span class="product-card__category">{{ strtoupper($brand->getName()) }}</span>
                            <h3 class="product-card__name">{{ $oemCode }}</h3>
                            @if($productName)
                            <p class="product-card__subtitle">{{ $productName }}</p>
                            @endif
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
