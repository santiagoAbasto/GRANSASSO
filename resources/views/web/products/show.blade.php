@extends('web.layouts.app')

@php
    $primaryItem   = $product->items->first();
    $oemCode       = $primaryItem?->oem_code ?? $product->code_gs ?? '—';
    $productName   = $product->getName()
        ? ucwords(mb_strtolower($product->getName()))
        : null;
    $rawDesc     = $product->getDescription();
    $productDesc = $rawDesc ? ucwords(mb_strtolower($rawDesc)) : null;

    // Figura: del producto o del primer item que la tenga
    $figure = $product->figure
        ?? $product->items->pluck('figure')->filter()->first();

    // Código GS
    $codeGs = $product->code_gs;

    // Referencias: del primer item que tenga dato
    $refOriginal = $product->items
        ->map(fn ($i) => $i->getReferenciaOriginal())
        ->filter()
        ->first();

    // Referencias adicionales: deduplicadas de todos los items
    $refsAdicionales = $product->items
        ->flatMap(fn ($i) => $i->getReferenciasAdicionales())
        ->filter()
        ->unique()
        ->values();

    // Variantes de caja (G60, G85, etc.)
    $variants = collect($product->getAvailableVariants())
        ->map(fn ($v) => strtoupper($v));

    // Aplicaciones de vehículo (las que NO son variantes de caja)
    $variantSet   = $variants->map(fn ($v) => strtolower($v))->toArray();
    $applications = $product->items
        ->filter(fn ($i) => $i->application !== null)
        ->pluck('application')
        ->unique()
        ->filter(fn ($app) => ! in_array(strtolower($app), $variantSet, true))
        ->values();

    // OEM codes alternativos (todos excepto el principal)
    $altOemCodes = $product->items
        ->pluck('oem_code')
        ->filter()
        ->unique()
        ->filter(fn ($oem) => $oem !== $oemCode)
        ->values();

    // Notas de items (deduplicadas, non-empty)
    $notes = $product->items
        ->pluck('notes')
        ->filter()
        ->unique()
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

                {{-- Nombre del producto --}}
                @if($productName)
                <p class="product-detail__name" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-style:normal;font-size:18px;font-weight:400;line-height:25px;margin-bottom:40px;">{{ $productName }}</p>
                @endif

                {{-- Descripción --}}
                @if($productDesc)
                <p class="product-detail__desc" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-style:normal;font-size:18px;font-weight:400;line-height:25px;margin-bottom:40px;">{{ $productDesc }}</p>
                @endif

                {{-- ── Tabla de datos ─────────────────────────────────── --}}
                <table class="product-detail__refs">

                    {{-- Código GS --}}
                    @if($codeGs)
                    <tr>
                        <td>Código GS</td>
                        <td>{{ $codeGs }}</td>
                    </tr>
                    @endif

                    {{-- Figura --}}
                    @if($figure)
                    <tr>
                        <td>Figura</td>
                        <td>{{ $figure }}</td>
                    </tr>
                    @endif

                    {{-- Modelo de caja --}}
                    <tr>
                        <td>Modelo de caja</td>
                        <td>{{ $gearbox->name }}</td>
                    </tr>

                    {{-- Vehículos compatibles de la caja --}}
                    @if($gearbox->compatible_vehicles)
                    <tr>
                        <td>Vehículos compatibles</td>
                        <td>{{ $gearbox->compatible_vehicles }}</td>
                    </tr>
                    @endif

                    {{-- Campos extra de la caja --}}
                    @if($gearbox->extra_field_1)
                    <tr>
                        <td>Información adicional</td>
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
                        <td>Modelos compatibles</td>
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

                    {{-- Notas --}}
                    @if($notes->isNotEmpty())
                    <tr>
                        <td>Notas</td>
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
                <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $rel->slug]) }}"
                   class="rel-card">

                    <div class="rel-card__img">
                        @if($rel->primaryImage)
                            <img src="{{ asset('storage/' . $rel->primaryImage->path) }}"
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
                        <h3 class="rel-card__name">{{ $relOem }}</h3>
                        @if($rel->getName())
                        <p class="rel-card__subtitle">{{ ucwords(mb_strtolower($rel->getName())) }}</p>
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
