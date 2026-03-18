@extends('web.layouts.app')
@section('title','Productos')

@section('content')

<section class="products-page">
    <div class="container">

        <div class="breadcrumb">
            <a href="{{ route('home') }}">Inicio</a>
            <span>›</span>
            <span>Productos</span>
        </div>

        {{-- BUSCADOR --}}
        <div class="products-search">
            <form action="{{ route('products.search') }}" method="GET" class="products-search__form">
                <div class="products-search__field">
                    <label>Marca</label>
                    <select name="marca">
                        <option value="">Seleccionar marca</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->slug }}">{{ $brand->getName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="products-search__field">
                    <label>Modelo</label>
                    <select name="modelo">
                        <option value="">Seleccionar modelo</option>
                    </select>
                </div>
                <div class="products-search__field">
                    <label>Código</label>
                    <input type="text" name="codigo" placeholder="Código">
                </div>
                <button type="submit" class="products-search__btn">Buscar</button>
            </form>
        </div>

        {{-- GRID DE MARCAS --}}
        <div class="brands-grid">
            @foreach($brands as $brand)
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

                <div class="brand-card__footer">
                    <span class="brand-card__compatible">Compatible con</span>
                    <strong class="brand-card__brand">{{ strtoupper($brand->getName()) }}</strong>
                </div>

            </a>
            @endforeach
        </div>

    </div>
</section>

@endsection
