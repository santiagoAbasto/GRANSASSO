@extends('web.layouts.app')
@section('title', $product->code_oem . ' - ' . $product->getNombre())

@section('content')

<section class="product-detail">
    <div class="container">

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Inicio</a>
            <span>›</span>
            <a href="{{ route('products.index') }}">Productos</a>
            <span>›</span>
            <a href="{{ route('products.brand', $brand->slug) }}">{{ $brand->getName() }}</a>
            <span>›</span>
            <a href="{{ route('products.gearbox', [$brand->slug, $gearbox->slug]) }}">{{ $gearbox->name }}</a>
            <span>›</span>
            <span>{{ $product->code_oem }}</span>
        </div>

        {{-- LAYOUT: galería + info --}}
        <div class="product-detail__layout">

            {{-- GALERÍA --}}
            <div class="product-detail__gallery">

                {{-- THUMBNAILS --}}
                <div class="product-detail__thumbs">
                    @forelse($product->images as $img)
                    <div class="product-detail__thumb {{ $loop->first ? 'active' : '' }}"
                         data-src="{{ asset('storage/' . $img->path) }}">
                        <img src="{{ asset('storage/' . $img->path) }}" alt="{{ $product->getNombre() }}">
                    </div>
                    @empty
                    <div class="product-detail__thumb active"
                         data-src="{{ asset('images/placeholder-product.png') }}">
                        <img src="{{ asset('images/placeholder-product.png') }}" alt="{{ $product->getNombre() }}">
                    </div>
                    @endforelse
                </div>

                {{-- IMAGEN PRINCIPAL --}}
                <div class="product-detail__main-img" id="productMainDisplay">
                    @php
                        $mainImg = $product->primaryImage ?? $product->images->first();
                        $mainSrc = $mainImg
                            ? asset('storage/' . $mainImg->path)
                            : asset('images/placeholder-product.png');
                    @endphp
                    <img src="{{ $mainSrc }}" alt="{{ $product->getNombre() }}" id="productMainImg">
                    <button class="product-detail__zoom" aria-label="Ampliar imagen">+</button>
                </div>

            </div>

            {{-- INFO --}}
            <div class="product-detail__info">

                <span class="product-detail__brand">{{ strtoupper($brand->getName()) }}</span>

                <h1 class="product-detail__code">{{ $product->code_oem }}</h1>

                @if($product->getNombre())
                <p class="product-detail__name">{{ $product->getNombre() }}</p>
                @endif

                @if($product->getDescription())
                <p class="product-detail__desc">{{ $product->getDescription() }}</p>
                @endif

                {{-- TABLA DE REFERENCIAS --}}
                <table class="product-detail__refs">
                    @if($product->referencia_original)
                    <tr>
                        <td>Referencia Original</td>
                        <td>{{ $product->referencia_original }}</td>
                    </tr>
                    @endif

                    @if($product->referencias_adicionales)
                    <tr>
                        <td>Referencia Adicional</td>
                        <td>{{ implode(' ; ', $product->referencias_adicionales) }}</td>
                    </tr>
                    @endif

                    @if($product->applications->count())
                    <tr>
                        <td>Aplicacion</td>
                        <td>
                            @foreach($product->applications as $app)
                            {{ $app->name }}<br>
                            @endforeach
                        </td>
                    </tr>
                    @endif
                </table>

                {{-- VARIANTES (G60, G85...) --}}
                @if($product->versions->count())
                <div class="product-detail__versions">
                    @foreach($product->versions->where('is_available', true) as $version)
                    <span class="product-detail__version-badge">{{ $version->version }}</span>
                    @endforeach
                </div>
                @endif

                {{-- ACCIONES --}}
                <div class="product-detail__actions">
                    @php $doc = $product->getTechnicalSheet(); @endphp
                    @if($doc)
                    <a href="{{ asset('storage/' . $doc->file) }}"
                       target="_blank"
                       class="btn btn--outline">Ficha técnica</a>
                    @else
                    <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $product->id]) }}?pdf=1"
                       class="btn btn--outline"
                       target="_blank">Ficha técnica</a>
                    @endif

                    <a href="{{ route('contact.index') }}?producto={{ $product->code_oem }}"
                       class="btn btn--primary">Consultar</a>
                </div>

            </div>
        </div>

        {{-- PRODUCTOS RELACIONADOS --}}
        @if($related->count())
        <div class="product-related">
            <h2 class="product-related__title">Productos relacionados</h2>
            <div class="products-grid">
                @foreach($related as $rel)
                <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $rel->id]) }}"
                   class="product-card">
                    <div class="product-card__img-wrap">
                        @if($rel->primaryImage)
                            <img src="{{ asset('storage/' . $rel->primaryImage->path) }}"
                                 alt="{{ $rel->getNombre() }}" loading="lazy">
                        @else
                            <img src="{{ asset('images/placeholder-product.png') }}"
                                 alt="{{ $rel->getNombre() }}" loading="lazy">
                        @endif
                        <div class="product-card__overlay"><span>+</span></div>
                    </div>
                    <div class="product-card__body">
                        <span class="product-card__category">{{ strtoupper($brand->getName()) }}</span>
                        <h3 class="product-card__name">{{ $rel->code_oem }}</h3>
                        @if($rel->getNombre())
                        <p class="product-card__subtitle">{{ $rel->getNombre() }}</p>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

{{-- GALLERY JS --}}
<script>
document.querySelectorAll('.product-detail__thumb').forEach(thumb => {
    thumb.addEventListener('click', function () {
        document.querySelectorAll('.product-detail__thumb').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('productMainImg').src = this.dataset.src;
    });
});
</script>

@endsection
