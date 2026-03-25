@extends('web.zona-privada.layouts.app')

@section('title', $novedad->title)

@section('content')

<div class="zp-page zp-page--narrow">
    <div class="zp-post">

        <a href="{{ route('zona-privada.novedades') }}" class="zp-post__back">
            ← {{ __('ui.zona_privada.novedades') }}
        </a>

        @if($novedad->category)
        <span class="zp-novedad-card__category">{{ strtoupper($novedad->category) }}</span>
        @endif

        <h1 class="zp-post__title">{{ $novedad->title }}</h1>

        @if($novedad->image)
        <div class="zp-post__image">
            <img src="{{ Storage::url($novedad->image) }}" alt="{{ $novedad->title }}">
        </div>
        @endif

        @if($novedad->excerpt)
        <p class="zp-post__excerpt">{{ $novedad->excerpt }}</p>
        @endif

        @if($novedad->body)
        <div class="zp-post__body">
            {!! $novedad->body !!}
        </div>
        @endif

    </div>
</div>

@endsection
