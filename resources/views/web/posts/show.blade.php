@extends('web.layouts.app')

@section('title', $post->title)

@section('content')

<section class="novedades-page novedades-show">

    <div class="container">

        {{-- BREADCRUMB --}}
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Inicio</a>
            <span>›</span>
            <a href="{{ route('posts.index') }}">Novedades</a>
            <span>›</span>
            <span>{{ $post->title }}</span>
        </div>

        {{-- ARTICLE --}}
        <article class="post-detail">

            {{-- CATEGORY + DATE --}}
            <div class="post-detail__meta">
                @if($post->category)
                <span class="post-detail__category">{{ strtoupper($post->category) }}</span>
                @endif
                @if($post->published_at)
                <span class="post-detail__date">{{ $post->published_at->format('d/m/Y') }}</span>
                @endif
            </div>

            {{-- TITLE --}}
            <h1 class="post-detail__title">{{ $post->title }}</h1>

            @if($post->excerpt)
            <p class="post-detail__excerpt">{{ $post->excerpt }}</p>
            @endif

            {{-- HERO IMAGE --}}
            @if($post->image)
            @php
            $urlPath = parse_url($post->image_url ?? '', PHP_URL_PATH) ?? '';
            $ext = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
            $isCover = in_array($ext, ['jpg', 'jpeg', 'webp']);
            @endphp
            <div class="post-detail__img-wrap {{ $isCover ? 'post-detail__img-wrap--cover' : '' }}">
                <img src="{{ $post->image_url }}" alt="{{ e($post->title) }}" class="post-detail__img">
            </div>
            @endif

            {{-- CONTENT --}}
            @if($post->content)
            <div class="post-detail__content">
                {!! $post->content !!}
            </div>
            @endif

        </article>

        {{-- RELATED POSTS --}}
        @if($related->count())
        <div class="post-detail__related">

            <h2 class="post-detail__related-title">Otras Novedades</h2>

            <div class="novedades__grid">

                @foreach($related as $item)

                @php
                $rUrlPath = parse_url($item->image_url ?? '', PHP_URL_PATH) ?? '';
                $rExt = strtolower(pathinfo($rUrlPath, PATHINFO_EXTENSION));
                $rIsCover = in_array($rExt, ['jpg', 'jpeg', 'webp']);
                @endphp

                <a href="{{ route('posts.show', $item->slug) }}" class="news-card">

                    <div class="news-card__img-wrap {{ $rIsCover ? 'news-card__img-wrap--cover' : '' }}">
                        <img src="{{ $item->image_url }}" alt="{{ e($item->title) }}" class="news-card__img" loading="lazy">
                    </div>

                    <div class="news-card__body">

                        @if($item->category)
                        <span class="news-card__category">{{ strtoupper($item->category) }}</span>
                        @endif

                        <h3 class="news-card__title">{{ $item->title }}</h3>

                        @if($item->excerpt)
                        <p class="news-card__excerpt">{{ $item->excerpt }}</p>
                        @endif

                        <div class="news-card__more">
                            <span class="news-card__more-text">Leer más</span>
                            <span class="news-card__arrow" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="#546F5D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        </div>

                    </div>

                </a>

                @endforeach

            </div>

        </div>
        @endif

    </div>

</section>

@endsection