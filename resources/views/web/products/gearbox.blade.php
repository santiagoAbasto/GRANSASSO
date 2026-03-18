@extends('web.layouts.app')
@section('title', $gearbox->name)

@section('content')

<section class="products-gearbox">
    <div class="container">

        <div class="breadcrumb">
            <a href="{{ route('home') }}">{{ __('ui.breadcrumb.home') }}</a>
            <span>›</span>
            <a href="{{ route('products.index') }}">{{ __('ui.breadcrumb.products') }}</a>
            <span>›</span>
            <a href="{{ route('products.brand', $brand->slug) }}">{{ $brand->getName() }}</a>
            <span>›</span>
            <span>{{ $gearbox->name }}</span>
        </div>

        <div class="products-layout">

            {{-- SIDEBAR marcas --}}
            <aside class="products-sidebar">
                @foreach($brands as $b)
                <a href="{{ route('products.brand', $b->slug) }}"
                   class="products-sidebar__item {{ $b->slug === $brand->slug ? 'active' : '' }}">
                    {{ strtoupper($b->getName()) }}
                </a>
                @endforeach
            </aside>

            {{-- GRID DE PRODUCTOS --}}
            <div class="products-grid">
                @forelse($products as $product)
                <a href="{{ route('products.show', [$brand->slug, $gearbox->slug, $product->id]) }}"
                   class="product-card">

                    <div class="product-card__img-wrap">
                        @if($product->primaryImage)
                            <img src="{{ asset('storage/' . $product->primaryImage->path) }}"
                                 alt="{{ $product->getNombre() }}"
                                 loading="lazy">
                        @elseif($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}"
                                 alt="{{ $product->getNombre() }}"
                                 loading="lazy">
                        @else
                            <img src="{{ asset('images/placeholder-product.png') }}"
                                 alt="{{ $product->getNombre() }}"
                                 loading="lazy">
                        @endif
                        <div class="product-card__overlay"><span class="product-card__plus">+</span></div>
                    </div>

                    <div class="product-card__body">
                        <span class="product-card__category">{{ strtoupper($brand->getName()) }}</span>
                        <h3 class="product-card__name">{{ $product->code_oem }}</h3>
                        @if($product->getNombre())
                        <p class="product-card__subtitle">{{ $product->getNombre() }}</p>
                        @endif
                    </div>

                </a>
                @empty
                <p>{{ __('ui.products.no_products') }}</p>
                @endforelse
            </div>

        </div>
    </div>
</section>

@endsection
