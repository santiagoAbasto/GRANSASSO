@extends('web.layouts.app')

@section('title',$subcategory->name)

@section('content')

<section class="products-subcategory">

<div class="container">

<div class="breadcrumb">

<a href="{{ route('home') }}">Inicio</a>

<span>›</span>

<a href="{{ route('products.index') }}">Instrumental</a>

<span>›</span>

<a href="{{ route('products.category', $subcategory->category->slug) }}">{{ $subcategory->category->name }}</a>

<span>›</span>

<span>{{ $subcategory->name }}</span>

</div>

<div class="products-grid products-grid--full">

@foreach($products as $product)

@include('web.products.components.product-card')

@endforeach

</div>

</div>

</section>

@endsection
