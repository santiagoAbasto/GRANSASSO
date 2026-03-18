@extends('web.layouts.app')

@section('title',$category->name)

@section('content')

<section class="products-category">

<div class="container">

<div class="breadcrumb">

<a href="{{ route('home') }}">Inicio</a>

<span>›</span>

<a href="{{ route('products.index') }}">Instrumental</a>

<span>›</span>

<span>{{ $category->name }}</span>

</div>


<div class="products-layout">


{{-- SIDEBAR --}}

<aside class="products-sidebar">

<h3>Categorías</h3>

<ul>

@foreach($categories as $cat)

<li style="--cat-color: {{ $cat->color }}" class="{{ $cat->slug == $category->slug ? 'active' : '' }}">

<a href="{{ route('products.category',$cat->slug) }}">

{{ strtoupper($cat->name) }}

</a>

</li>

@endforeach

</ul>

</aside>



{{-- SUBCATEGORIAS --}}

<div class="products-grid">

@foreach($subcategories as $subcategory)

<a href="{{ route('products.subcategory',[$category->slug,$subcategory->slug]) }}" class="product-card" style="--cat-color: {{ $category->color }}">

<div class="product-card-image">

<img src="{{ asset('storage/'.$subcategory->image) }}" alt="{{ $subcategory->name }}">

<div class="product-card-overlay"><span class="product-card-plus">+</span></div>

</div>

<div class="product-card-body">

<span class="product-category">

{{ strtoupper($category->name) }}

</span>

<h3>

{{ $subcategory->name }}

</h3>

</div>

</a>

@endforeach

</div>

</div>

</div>

</section>

@endsection