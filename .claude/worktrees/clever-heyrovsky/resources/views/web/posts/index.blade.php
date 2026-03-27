@extends('web.layouts.app')

@section('title', 'Novedades')

@section('content')

<section class="novedades-page">

<div class="container">

<div class="breadcrumb">
    <a href="{{ route('home') }}">Inicio</a>
    <span>›</span>
    <span>Novedades</span>
</div>

<div class="novedades__grid">

    @foreach($posts as $post)

    @php
        $urlPath = parse_url($post->image_url ?? '', PHP_URL_PATH) ?? '';
        $ext     = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
        $isCover = in_array($ext, ['jpg', 'jpeg', 'webp']);
    @endphp

    <a href="{{ route('posts.show', $post->slug) }}" class="news-card">

        <div class="news-card__img-wrap {{ $isCover ? 'news-card__img-wrap--cover' : '' }}">
            <img
                src="{{ $post->image_url }}"
                alt="{{ e($post->title) }}"
                class="news-card__img"
                loading="lazy">
        </div>

        <div class="news-card__body">

            @php $cat = $post->getLocalizedCategory(); @endphp
            @if($cat)
            <span class="news-card__category">{{ strtoupper($cat) }}</span>
            @endif

            <h3 class="news-card__title">{{ $post->title }}</h3>

            @if($post->excerpt)
            <p class="news-card__excerpt">{{ $post->excerpt }}</p>
            @endif

            <div class="news-card__more">
                <span class="news-card__more-text">Leer más</span>
                <span class="news-card__arrow" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="#546F5D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
            </div>

        </div>

    </a>

    @endforeach

</div>

@if($posts->hasPages())
<div class="novedades-page__pagination">
    {{ $posts->links() }}
</div>
@endif

</div>

</section>

@endsection
