@extends('web.layouts.app')

@section('title', $novedad->getLocalizedTitle())

@php
$bannerSection = $page?->sections->where('type', 'banner')->first();
$bannerFallback = $bannerSection?->images->sortBy('order')->first()?->image;
$bannerImage = $novedad->image_url ?: ($bannerFallback ? asset('storage/' . $bannerFallback) : null);
$detailImages = array_values(array_filter(array_unique(array_filter([
    $novedad->image_url,
    ...collect($novedad->galleryImages ?? [])->pluck('image_url')->all(),
]))));
$hasSingleDetailImage = count($detailImages) === 1;
$category = $novedad->getLocalizedCategory();
$excerpt = trim((string) $novedad->getLocalizedExcerpt());
$body = trim(strip_tags((string) $novedad->getLocalizedBody()));
$metaDescription = \Illuminate\Support\Str::limit($excerpt ?: $body, 160, '');
$metaKeywords = implode(', ', array_filter([
    $novedad->getLocalizedTitle(),
    $category,
    'Gran Sasso',
    'novedades',
]));
@endphp

@section('meta_description', $metaDescription)
@section('meta_keywords', $metaKeywords)
@section('meta_image', $bannerImage ?: asset('images/og/gran-sasso-default.png'))
@section('canonical_url', route('novedades.show', $novedad))
@section('og_type', 'article')
@section('article_published_time', optional($novedad->created_at)->toIso8601String())

@section('content')

<section class="novedades-public-banner novedades-public-banner--detail" data-slides="1">
    <div
        class="novedades-public-banner__slide active"
        @if($bannerImage)
            style="background: linear-gradient(0deg, rgba(0, 0, 0, 0.10) 0%, rgba(0, 0, 0, 0.10) 100%), url('{{ $bannerImage }}') lightgray 50% / cover no-repeat;"
        @else
            style="background-color:#0D2038"
        @endif
    ></div>

    <div class="novedades-public-banner__overlay"></div>

    <div class="novedades-public-banner__inner">
        <div class="novedades-public-banner__topbar">
            <nav class="novedades-public-banner__breadcrumb" aria-label="{{ __('ui.novedades.bc_aria') }}">
                <a href="{{ route('home') }}" class="novedades-public-banner__bc-home">{{ __('ui.breadcrumb.home') }}</a>
                <svg class="novedades-public-banner__bc-sep" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.5 9L7.5 6L4.5 3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <a href="{{ route('novedades') }}" class="novedades-public-banner__bc-home">{{ __('ui.breadcrumb.novedades') }}</a>
                <svg class="novedades-public-banner__bc-sep" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.5 9L7.5 6L4.5 3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="novedades-public-banner__bc-current">{{ \Illuminate\Support\Str::limit($novedad->getLocalizedTitle(), 44) }}</span>
            </nav>
        </div>

        <div class="novedades-public-banner__bottom">
            <h1 class="novedades-public-banner__title">{{ $novedad->getLocalizedTitle() }}</h1>
        </div>
    </div>
</section>

<section class="novedades-public-detail">
    <div class="container">
        <article class="novedades-public-detail__article{{ $hasSingleDetailImage ? ' novedades-public-detail__article--single' : '' }}">
            @if(count($detailImages) > 1)
            <aside class="novedades-public-detail__media">
                @foreach($detailImages as $image)
                <div class="novedades-public-detail__media-item">
                    <img
                        src="{{ $image }}"
                        alt="{{ $novedad->getLocalizedTitle() }}"
                        class="novedades-public-detail__media-image"
                    >
                </div>
                @endforeach
            </aside>
            @endif

            <div class="novedades-public-detail__content">
                @if($hasSingleDetailImage)
                <div class="novedades-public-detail__hero-image-wrap">
                    <img
                        src="{{ $detailImages[0] }}"
                        alt="{{ $novedad->getLocalizedTitle() }}"
                        class="novedades-public-detail__hero-image"
                    >
                </div>
                @endif

                @if($category)
                <span class="novedades-public-detail__category">{{ $category }}</span>
                @endif

                @if($excerpt)
                <p class="novedades-public-detail__excerpt">{!! nl2br(e($excerpt)) !!}</p>
                @endif

                @php $renderedBody = $novedad->getLocalizedBody(); @endphp
                @if($renderedBody)
                <div class="novedades-public-detail__body">
                    {!! $renderedBody !!}
                </div>
                @endif

                <div class="novedades-public-detail__back-wrap">
                    <a href="{{ route('novedades') }}" class="novedades-public-detail__back">
                        ← {{ __('ui.novedades.back') }}
                    </a>
                </div>
            </div>
        </article>

        @if($related->isNotEmpty())
        <section class="novedades-public-related">
            <h2 class="novedades-public-related__title">{{ __('ui.novedades.page_title') }}</h2>
            <div class="novedades-public-grid">
                @foreach($related as $item)
                <a href="{{ route('novedades.show', $item) }}" class="novedades-public-card">
                    @if($item->image)
                    <div class="novedades-public-card__image-wrap">
                        <img src="{{ $item->image_url }}" alt="{{ $item->getLocalizedTitle() }}" class="novedades-public-card__image" loading="lazy">
                    </div>
                    @else
                    <div class="novedades-public-card__image-wrap novedades-public-card__image-wrap--empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C0C0C0" stroke-width="1.5" aria-hidden="true">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                    </div>
                    @endif

                    <div class="novedades-public-card__body">
                        @php $relatedCategory = $item->getLocalizedCategory(); @endphp
                        @if($relatedCategory)
                        <span class="novedades-public-card__category">{{ $relatedCategory }}</span>
                        @endif

                        <h3 class="novedades-public-card__title">{{ $item->getLocalizedTitle() }}</h3>

                        @php $relatedExcerpt = $item->getLocalizedExcerpt(); @endphp
                        @if($relatedExcerpt)
                        <p class="novedades-public-card__excerpt">{{ strip_tags($relatedExcerpt) }}</p>
                        @endif

                        <div class="novedades-public-card__more">
                            <span>{{ __('ui.novedades.read_more') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                                <path d="M8 0L6.59 1.41L12.17 7H0V9H12.17L6.59 14.59L8 16L16 8L8 0Z" fill="#414047"/>
                            </svg>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</section>

@endsection
