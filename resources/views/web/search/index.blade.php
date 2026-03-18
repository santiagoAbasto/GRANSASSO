@extends('web.layouts.app')

@section('title', $q ? "Búsqueda: {$q}" : 'Buscador')

@section('content')

<section class="search-page">
<div class="container">

    {{-- BREADCRUMB --}}
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Inicio</a>
        <span>›</span>
        <span>Buscador</span>
    </div>

    {{-- FORM DE BÚSQUEDA --}}
    <form action="{{ route('search') }}" method="GET" class="search-page__form">
        <div class="search-page__input-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M21 21L16.66 16.66M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="#136CBA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <input
                type="text"
                name="q"
                value="{{ $q }}"
                placeholder="Buscar productos, categorías, marcas..."
                class="search-page__input"
                autofocus
                autocomplete="off"
            >
            @if($q)
            <a href="{{ route('search') }}" class="search-page__clear" aria-label="Limpiar búsqueda">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18M6 6L18 18" stroke="#999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            @endif
        </div>
        <button type="submit" class="search-page__btn">Buscar</button>
    </form>

    {{-- ENCABEZADO DE RESULTADOS --}}
    <div class="search-page__meta">
        @if($q)
            <p class="search-page__count">
                <strong>{{ $results->count() }}</strong> resultado{{ $results->count() !== 1 ? 's' : '' }}
                para <span class="search-page__query">"{{ $q }}"</span>
            </p>
        @else
            <p class="search-page__count">
                <strong>{{ $results->count() }}</strong> productos disponibles
            </p>
        @endif
    </div>

    {{-- GRID DE RESULTADOS --}}
    @if($results->isEmpty())
        <div class="search-page__empty">
            <p>No encontramos productos que coincidan con <strong>"{{ $q }}"</strong>.</p>
            <p>Intentá con otras palabras o explorá nuestros <a href="{{ route('products.index') }}">productos</a>.</p>
        </div>
    @else
        <div class="search-page__grid products-subcategory">
            @foreach($results as $product)
            <a href="{{ route('products.show', $product->slug) }}" class="product-card" style="--cat-color: {{ $product->category->color ?? '#28597D' }}">

                <div class="product-card-image">
                    <img
                        src="{{ $product->imageUrl }}"
                        alt="{{ $product->name }}"
                    >
                    <div class="product-card-overlay">
                        <span class="product-card-plus">+</span>
                    </div>
                </div>

                <div class="product-card-body">

                    @if($product->category)
                    <span class="product-category">
                        @php
                            $catName = $product->category->name;
                            echo e(strtoupper(is_array($catName) ? ($catName['es'] ?? reset($catName)) : $catName));
                        @endphp
                    </span>
                    @endif

                    <h3 class="product-title">{{ $product->name }}</h3>

                    @if($product->short_description)
                    <p class="product-subtitle">{{ $product->short_description }}</p>
                    @endif

                </div>

            </a>
            @endforeach
        </div>
    @endif

</div>
</section>

@endsection
