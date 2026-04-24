@extends('web.layouts.app')

@section('title', __('ui.novedades.page_title'))

@section('content')

@php
$locale = session('locale', 'es');
$bannerSection = $page?->sections->where('type', 'banner')->first();
$bannerSlides = $bannerSection?->images->sortBy('order') ?? collect();
$introSection = $page?->sections->where('type', 'novedades_intro')->first();

$novedadesText = function ($value, string $locale, string $fallback = ''): string {
    if (is_array($value)) {
        return $value[$locale] ?? $value['es'] ?? (reset($value) ?: $fallback);
    }

    return $value ?: $fallback;
};

$introTitle = $novedadesText($introSection?->title, $locale, __('ui.novedades.intro_title'));
$introCopy = $novedadesText($introSection?->content, $locale, '<p>' . __('ui.novedades.intro_copy') . '</p>');
@endphp

<section class="novedades-public-banner" data-slides="{{ $bannerSlides->count() }}">
    @if($bannerSlides->count())
        @foreach($bannerSlides as $i => $slide)
        <div
            class="novedades-public-banner__slide {{ $i === 0 ? 'active' : '' }}"
            data-index="{{ $i }}"
            @if($slide->image)
                style="background: linear-gradient(0deg, rgba(0, 0, 0, 0.10) 0%, rgba(0, 0, 0, 0.10) 100%), url('{{ asset('storage/' . $slide->image) }}') lightgray 50% / cover no-repeat;"
            @endif
        ></div>
        @endforeach
    @else
        <div class="novedades-public-banner__slide active" style="background-color:#0D2038"></div>
    @endif

    <div class="novedades-public-banner__overlay"></div>

    <div class="novedades-public-banner__inner">
        <div class="novedades-public-banner__topbar">
            <nav class="novedades-public-banner__breadcrumb" aria-label="{{ __('ui.novedades.bc_aria') }}">
                <a href="{{ route('home') }}" class="novedades-public-banner__bc-home">{{ __('ui.breadcrumb.home') }}</a>
                <svg class="novedades-public-banner__bc-sep" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4.5 9L7.5 6L4.5 3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="novedades-public-banner__bc-current">{{ __('ui.breadcrumb.novedades') }}</span>
            </nav>
        </div>

        <div class="novedades-public-banner__bottom">
            <h1 class="novedades-public-banner__title">{{ __('ui.novedades.page_title') }}</h1>

            @if($bannerSlides->count() >= 2)
            <div class="novedades-public-banner__dots">
                @foreach($bannerSlides as $i => $slide)
                <button
                    class="novedades-public-banner__dot {{ $i === 0 ? 'active' : '' }}"
                    data-index="{{ $i }}"
                    aria-label="{{ __('ui.novedades.slide_aria', ['num' => $i + 1]) }}"
                ></button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

<section class="novedades-public-intro">
    <div class="container">
        <div class="novedades-public-intro__inner">
            @if($introTitle)
            <h2 class="novedades-public-intro__title">{{ $introTitle }}</h2>
            @endif

            <div class="novedades-public-intro__copy">
                {!! $introCopy !!}
            </div>
        </div>
    </div>
</section>

<section class="novedades-public-list">
    <div class="container">
        @if($novedades->isEmpty())
        <div class="novedades-public-empty">{{ __('ui.novedades.empty') }}</div>
        @else
        <div class="novedades-public-grid">
            @foreach($novedades as $novedad)
            <a href="{{ route('novedades.show', $novedad) }}" class="novedades-public-card">
                @if($novedad->image)
                <div class="novedades-public-card__image-wrap">
                    <img
                        src="{{ $novedad->image_url }}"
                        alt="{{ $novedad->getLocalizedTitle() }}"
                        class="novedades-public-card__image"
                        loading="lazy"
                    >
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
                    @php $category = $novedad->getLocalizedCategory(); @endphp
                    @if($category)
                    <span class="novedades-public-card__category">{{ $category }}</span>
                    @endif

                    <h3 class="novedades-public-card__title">{{ $novedad->getLocalizedTitle() }}</h3>

                    @php $excerpt = $novedad->getLocalizedExcerpt(); @endphp
                    @if($excerpt)
                    <p class="novedades-public-card__excerpt">{{ strip_tags($excerpt) }}</p>
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

        @if($novedades->hasPages())
        <div class="novedades-public-pagination">
            {{ $novedades->links() }}
        </div>
        @endif
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    const banner = document.querySelector('.novedades-public-banner');
    if (!banner) return;

    const slides = banner.querySelectorAll('.novedades-public-banner__slide');
    const dots = banner.querySelectorAll('.novedades-public-banner__dot');
    if (slides.length <= 1) return;

    let current = 0;
    let timer = null;

    function goTo(index) {
        slides[current].classList.remove('active');
        dots[current]?.classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        dots[current]?.classList.add('active');
    }

    function startTimer() {
        timer = setInterval(() => goTo(current + 1), 5000);
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            clearInterval(timer);
            goTo(i);
            startTimer();
        });
    });

    startTimer();
})();
</script>
@endpush
