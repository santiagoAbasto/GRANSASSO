@extends('web.layouts.app')
@section('title', 'Búsqueda: ' . $code)

@section('content')

<section class="products-body">
    <div class="container">

        {{-- BREADCRUMB --}}
        <nav class="products-bc" aria-label="{{ __('ui.products.bc_aria') }}">
            <a href="{{ route('home') }}" class="products-bc__link">{{ __('ui.breadcrumb.home') }}</a>
            <svg class="products-bc__sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M6 3L11 8L6 13" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <a href="{{ route('products.index') }}" class="products-bc__link">{{ __('ui.breadcrumb.products') }}</a>
            <svg class="products-bc__sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                <path d="M6 3L11 8L6 13" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="products-bc__current">Búsqueda</span>
        </nav>

        {{-- LAYOUT: SIDEBAR + MAIN --}}
        <div class="products-layout">

            {{-- SIDEBAR --}}
            <aside class="products-sidebar">
                @foreach($brands as $b)
                <a href="{{ route('products.brand', $b->slug) }}"
                   class="products-sidebar__item {{ $b->slug === $brandSlug ? 'active' : '' }}">
                    {{ strtoupper($b->getName()) }}
                </a>
                @endforeach
            </aside>

            {{-- MAIN --}}
            <div class="products-main">

                {{-- Encabezado --}}
                <div class="products-search-header">
                    <p class="products-search-header__text">
                        <strong>{{ $products->count() }}</strong>
                        {{ $products->count() === 1 ? 'resultado' : 'resultados' }}
                        para el código <span class="products-search-header__code">{{ $code }}</span>
                    </p>
                    <a href="{{ route('products.index') }}" class="products-search-header__back">
                        ← Volver a productos
                    </a>
                </div>

                @if($products->isEmpty())

                <div class="products-empty">
                    <p>No encontramos productos con el código <strong>{{ $code }}</strong>.</p>
                    <a href="{{ route('products.index') }}" class="btn--outline" style="margin-top:16px;display:inline-flex;">
                        Ver todos los productos
                    </a>
                </div>

                @else

                <div class="products-grid">
                    @foreach($products as $product)
                    @php
                        $gearbox     = $product->gearbox;
                        $brand       = $gearbox?->brand;
                        $card        = $product->catalog_card ?? [];
                        $titleCode   = $card['title_code'] ?? ($product->code_gs ?? '—');
                        $oemCode     = $card['primary_oem_code'] ?? $titleCode;
                        $productName = ! empty($card['description'])
                            ? ucwords(mb_strtolower($card['description']))
                            : null;
                    @endphp

                    @if($gearbox && $brand)
                    <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $product->code_gs ?? $product->slug]) }}"
                       class="prd-card">

                        {{-- IMAGEN --}}
                        <div class="prd-card__img">
                            @if($product->resolved_image_url)
                                <img src="{{ $product->resolved_image_url }}"
                                     alt="{{ $oemCode }}" loading="lazy">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64"
                                     viewBox="0 0 24 24" fill="none" stroke="#D1D5DB"
                                     stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                                    <circle cx="8.5" cy="8.5" r="1.5"/>
                                    <polyline points="21 15 16 10 5 21"/>
                                </svg>
                            @endif
                            <div class="prd-card__overlay"></div>
                            <span class="prd-card__plus">+</span>
                        </div>

                        <span class="prd-card__divider"></span>

                        {{-- INFO --}}
                        <div class="prd-card__body">
                            <span class="prd-card__brand">{{ strtoupper($brand->getName()) }} · {{ $gearbox->name }}</span>
                            <h3 class="prd-card__name">{{ $titleCode }}</h3>
                            @if($productName)
                            <p class="prd-card__subtitle">{{ $productName }}</p>
                            @endif
                        </div>

                    </a>
                    @endif
                    @endforeach
                </div>

                @endif

            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
(function () {
    /* Marca seleccionada al volver */
    var marcaSelect  = document.querySelector('.hero-search__select[name="marca"]') || document.querySelector('.products-search__select[name="marca"]');
    var modeloSelect = document.querySelector('.hero-search__select--modelo') || document.querySelector('.products-search__select--modelo');

    if (!marcaSelect || !modeloSelect) return;

    var brandVal = '{{ $brandSlug }}';
    var modelVal = '{{ $gearboxSlug }}';

    if (brandVal) {
        marcaSelect.value = brandVal;
        fetch('/api/gearboxes?brand=' + encodeURIComponent(brandVal))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                data.forEach(function (g) {
                    var opt = document.createElement('option');
                    opt.value = g.slug;
                    opt.textContent = g.name;
                    if (g.slug === modelVal) opt.selected = true;
                    modeloSelect.appendChild(opt);
                });
            })
            .catch(function () {});
    }
}());
</script>
@endpush

@endsection
