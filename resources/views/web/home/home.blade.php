@extends('web.layouts.app')

@section('title', 'Inicio')

@section('content')

@include('web.home.sections.hero')

@include('web.home.sections.hero-search')

@include('web.home.sections.productos-destacados')

@include('web.home.sections.about')

@include('web.home.sections.industrias')

@include('web.home.sections.novedades')

@include('web.home.sections.contacto')

@endsection