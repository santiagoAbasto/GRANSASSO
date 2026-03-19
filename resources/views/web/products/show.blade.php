@extends('web.layouts.app')

@php
    /* ── Datos del producto ───────────────────────────────────────────────
     * Con el nuevo modelo (ProductItem), un Product tiene N items.
     * La ficha muestra el primer item como "principal" y agrega
     * referencias / aplicaciones de todos los items.
     * ─────────────────────────────────────────────────────────────────── */

    $primaryItem   = $product->items->first();
    $oemCode       = $primaryItem?->oem_code ?? $product->code_gs ?? '—';
    $productName   = $product->getName();
    $productDesc   = $product->getDescription();

    // Referencias: del primer item que tenga dato
    $refOriginal   = $product->items
        ->map(fn ($i) => $i->getReferenciaOriginal())
        ->filter()
        ->first();

    // Referencias adicionales: deduplicadas de todos los items
    $refsAdicionales = $product->items
        ->flatMap(fn ($i) => $i->getReferenciasAdicionales())
        ->filter()
        ->unique()
        ->values();

    // ── Variantes de caja (G60, G85, etc.) ─────────────────────────────
    // Con la nueva arquitectura cada variante = 1 item con application="G60".
    // Se muestran como badges de compatibilidad.
    $variants = collect($product->getAvailableVariants())
        ->map(fn ($v) => strtoupper($v));

    // ── Aplicaciones de vehículo ────────────────────────────────────────
    // Solo aplica al formato plano donde modelos_vehiculo viene por fila.
    // En el formato catálogo las applications SON las variantes (ya mostradas
    // como badges), así que las excluimos para evitar duplicar la información.
    $variantSet   = $variants->map(fn ($v) => strtolower($v))->toArray();
    $applications = $product->items
        ->filter(fn ($i) => $i->application !== null)
        ->pluck('application')
        ->unique()
        ->filter(fn ($app) => ! in_array(strtolower($app), $variantSet, true))
        ->values();

    // ── OEM codes alternativos ──────────────────────────────────────────
    // Todos los OEM únicos del producto EXCEPTO el principal.
    // Con el nuevo modelo un producto puede tener el mismo OEM en G60 y G85
    // (mismo OEM, distinta application) → se deduplica correctamente.
    $altOemCodes = $product->items
        ->pluck('oem_code')
        ->filter()
        ->unique()
        ->filter(fn ($oem) => $oem !== $oemCode)
        ->values();
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
                    @forelse($product->images as $img)
                    <div class="product-detail__thumb {{ $loop->first ? 'active' : '' }}"
                         data-src="{{ asset('storage/' . $img->path) }}"
                         role="button"
                         tabindex="0"
                         aria-label="Imagen {{ $loop->iteration }}">
                        <img src="{{ asset('storage/' . $img->path) }}"
                             alt="{{ $oemCode }} — imagen {{ $loop->iteration }}">
                    </div>
                    @empty
                    <div class="product-detail__thumb active"
                         data-src="{{ asset('images/placeholder-product.png') }}">
                        <img src="{{ asset('images/placeholder-product.png') }}"
                             alt="{{ $oemCode }}">
                    </div>
                    @endforelse
                </div>

                {{-- Imagen principal --}}
                <div class="product-detail__main-img" id="productMainDisplay">
                    @php
                        $mainImg = $product->primaryImage ?? $product->images->first();
                        $mainSrc = $mainImg
                            ? asset('storage/' . $mainImg->path)
                            : asset('images/placeholder-product.png');
                    @endphp
                    <img src="{{ $mainSrc }}"
                         alt="{{ $oemCode }}"
                         id="productMainImg">
                    <button class="product-detail__zoom"
                            aria-label="{{ __('ui.product_detail.zoom') }}">
                        +
                    </button>
                </div>

            </div>

            {{-- INFO --}}
            <div class="product-detail__info">

                {{-- Marca --}}
                <span class="product-detail__brand">{{ strtoupper($brand->getName()) }}</span>

                {{-- OEM code como título principal --}}
                <h1 class="product-detail__code">{{ $oemCode }}</h1>

                {{-- Nombre del producto --}}
                @if($productName)
                <p class="product-detail__name">{{ $productName }}</p>
                @endif

                {{-- Descripción --}}
                @if($productDesc)
                <p class="product-detail__desc">{{ $productDesc }}</p>
                @endif

                {{-- ── Tabla de referencias ──────────────────────────── --}}
                <table class="product-detail__refs">

                    @if($refOriginal)
                    <tr>
                        <td>{{ __('ui.product_detail.original_ref') }}</td>
                        <td>{{ $refOriginal }}</td>
                    </tr>
                    @endif

                    @if($refsAdicionales->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.additional_ref') }}</td>
                        <td>{{ $refsAdicionales->implode(' ; ') }}</td>
                    </tr>
                    @endif

                    @if($altOemCodes->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.alt_oem') }}</td>
                        <td>{{ $altOemCodes->implode(' / ') }}</td>
                    </tr>
                    @endif

                    @if($applications->isNotEmpty())
                    <tr>
                        <td>{{ __('ui.product_detail.application') }}</td>
                        <td>
                            @foreach($applications as $app)
                            {{ $app }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </td>
                    </tr>
                    @endif

                </table>

                {{-- ── Variantes / compatibilidad ───────────────────── --}}
                @if($variants->isNotEmpty())
                <div class="product-detail__versions">
                    @foreach($variants as $variant)
                    <span class="product-detail__version-badge">{{ $variant }}</span>
                    @endforeach
                </div>
                @endif

                {{-- ── Acciones ──────────────────────────────────────── --}}
                <div class="product-detail__actions">

                    @php $doc = $product->getTechnicalSheet(); @endphp

                    @if($doc)
                    <a href="{{ asset('storage/' . $doc->file) }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn--outline">
                        {{ __('ui.product_detail.technical_sheet') }}
                    </a>
                    @else
                    <a href="{{ route('contact.index') }}?tipo=ficha&producto={{ $oemCode }}"
                       class="btn btn--outline">
                        {{ __('ui.product_detail.technical_sheet') }}
                    </a>
                    @endif

                    <a href="{{ route('contact.index') }}?producto={{ $oemCode }}"
                       class="btn btn--primary">
                        {{ __('ui.product_detail.inquire') }}
                    </a>

                </div>

            </div>
        </div>

        {{-- ══ PRODUCTOS RELACIONADOS ═════════════════════════════════════ --}}
        @if($related->count())
        <div class="product-related">
            <h2 class="product-related__title">{{ __('ui.product_detail.related') }}</h2>
            <div class="products-grid">
                @foreach($related as $rel)
                @php
                    $relItem = $rel->items->first();
                    $relOem  = $relItem?->oem_code ?? $rel->code_gs ?? '—';
                @endphp
                <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $rel->id]) }}"
                   class="product-card">

                    <div class="product-card__img-wrap">
                        @if($rel->primaryImage)
                            <img src="{{ asset('storage/' . $rel->primaryImage->path) }}"
                                 alt="{{ $relOem }}" loading="lazy">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48"
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

                    <div class="product-card__body">
                        <span class="product-card__category">{{ strtoupper($brand->getName()) }}</span>
                        <h3 class="product-card__name">{{ $relOem }}</h3>
                        @if($rel->getName())
                        <p class="product-card__subtitle">{{ $rel->getName() }}</p>
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
