@extends('web.layouts.app')
@section('title', $brand->getName())

@section('content')

<section class="products-brand">
    <div class="container">

        <div class="breadcrumb">
            <a href="{{ route('home') }}">Inicio</a>
            <span>›</span>
            <a href="{{ route('products.index') }}">Productos</a>
            <span>›</span>
            <span>{{ $brand->getName() }}</span>
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

            {{-- GRID DE CAJAS --}}
            <div class="products-grid">
                @forelse($gearboxes as $gearbox)
                <a href="{{ route('products.gearbox', [$brand->slug, $gearbox->slug]) }}" class="product-card">

                    <div class="product-card__img-wrap">
                        @if($gearbox->image)
                            <img src="{{ asset('storage/' . $gearbox->image) }}"
                                 alt="{{ $gearbox->name }}"
                                 loading="lazy">
                        @else
                            <img src="{{ asset('storage/images/logos/' . Str::slug($brand->getName()) . '.png') }}"
                                 alt="{{ $brand->getName() }}"
                                 loading="lazy">
                        @endif
                        <div class="product-card__overlay"><span class="product-card__plus">+</span></div>
                    </div>

                    <div class="product-card__body">
                        <span class="product-card__category">{{ strtoupper($brand->getName()) }}</span>
                        <h3 class="product-card__name">{{ $gearbox->name }}</h3>
                    </div>

                </a>
                @empty
                <p>No hay modelos disponibles para esta marca.</p>
                @endforelse
            </div>

        </div>
    </div>
</section>

@endsection
