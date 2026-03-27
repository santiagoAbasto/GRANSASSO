@extends('web.layouts.app')

@section('title', 'Industrias')

@section('content')

<section class="industries-page">

<div class="container">

<div class="breadcrumb">
    <a href="{{ route('home') }}">Inicio</a>
    <span>›</span>
    <span>Industrias</span>
</div>

<div class="industries-grid">

    @foreach($industries as $industry)
    <div class="industry-card">

        @if($industry->image)
        <img src="{{ asset('storage/' . $industry->image) }}" alt="{{ $industry->name }}" class="industry-card__img">
        @endif

        <div class="industry-card__overlay"></div>

        <div class="industry-card__plus"></div>

        <span class="industry-card__name">{{ $industry->name }}</span>

    </div>
    @endforeach

</div>

</div>

</section>

@endsection
