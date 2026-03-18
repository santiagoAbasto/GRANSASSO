@extends('web.layouts.app')
@section('title', __('ui.products.title'))

@section('content')

<section class="products-page">
    <div class="container">

        <div class="breadcrumb">
            <a href="{{ route('home') }}">{{ __('ui.breadcrumb.home') }}</a>
            <span>›</span>
            <span>{{ __('ui.breadcrumb.products') }}</span>
        </div>

        {{-- BUSCADOR --}}
        <div class="products-search">
            <form action="{{ route('products.search') }}" method="GET" class="products-search__form">
                <div class="products-search__field">
                    <label>{{ __('ui.search.brand') }}</label>
                    <select name="marca">
                        <option value="">{{ __('ui.search.select_brand') }}</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->slug }}">{{ $brand->getName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="products-search__field">
                    <label>{{ __('ui.search.model') }}</label>
                    <select name="modelo">
                        <option value="">{{ __('ui.search.select_model') }}</option>
                    </select>
                </div>
                <div class="products-search__field">
                    <label>{{ __('ui.search.code') }}</label>
                    <input type="text" name="codigo" placeholder="{{ __('ui.search.code_placeholder') }}">
                </div>
                <button type="submit" class="products-search__btn">{{ __('ui.search.button') }}</button>
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
                    <span class="brand-card__compatible">{{ __('ui.products.compatible_with') }}</span>
                    <strong class="brand-card__brand">{{ strtoupper($brand->getName()) }}</strong>
                </div>

            </a>
            @endforeach
        </div>

    </div>
</section>

@endsection
