@extends('web.layouts.app')

@section('title', __('ui.calidad.page_title'))

@section('content')

@php
$locale         = session('locale', 'es');
$bannerSection  = $page?->sections->where('type', 'banner')->first();
$bannerSlides   = $bannerSection?->images->sortBy('order') ?? collect();
$mainSection    = $page?->sections->where('type', 'calidad_main')->first();
$certImage      = $mainSection?->images->first();

function calidad_text($value, string $locale = 'es'): string
{
    if (is_array($value)) {
        return $value[$locale] ?? $value['es'] ?? (reset($value) ?: '');
    }
    return $value ?? '';
}
@endphp


{{-- ════════════════════════════════════════
     BANNER
════════════════════════════════════════ --}}
<section class="calidad-banner" data-slides="{{ $bannerSlides->count() }}">

    @if($bannerSlides->count())
        @foreach($bannerSlides as $i => $slide)
        <div class="calidad-banner__slide {{ $i === 0 ? 'active' : '' }}"
             data-index="{{ $i }}"
             @if($slide->image)
                 style="background: linear-gradient(0deg, rgba(0, 0, 0, 0.10) 0%, rgba(0, 0, 0, 0.10) 100%), url('{{ asset('storage/' . $slide->image) }}') lightgray 50% / cover no-repeat;"
             @endif>
        </div>
        @endforeach
    @else
        <div class="calidad-banner__slide active" style="background-color:#0D2038"></div>
    @endif

    <div class="calidad-banner__overlay"></div>

    <div class="calidad-banner__inner">

        <div class="calidad-banner__topbar">
            <nav class="calidad-banner__breadcrumb" aria-label="{{ __('ui.calidad.bc_aria') }}">
                <a href="{{ route('home') }}" class="calidad-banner__bc-home">{{ __('ui.breadcrumb.home') }}</a>
                <svg class="calidad-banner__bc-sep" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:1/1;flex-shrink:0;">
                    <path d="M4.5 9L7.5 6L4.5 3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="calidad-banner__bc-current">{{ __('ui.breadcrumb.calidad') }}</span>
            </nav>
        </div>

        <div class="calidad-banner__bottom">
            <h1 class="calidad-banner__title">{{ __('ui.calidad.page_title') }}</h1>

            @if($bannerSlides->count() >= 2)
            <div class="calidad-banner__dots">
                @foreach($bannerSlides as $i => $slide)
                <button
                    class="calidad-banner__dot {{ $i === 0 ? 'active' : '' }}"
                    data-index="{{ $i }}"
                    aria-label="{{ __('ui.calidad.slide_aria', ['num' => $i + 1]) }}"></button>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</section>


{{-- ════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════ --}}
@if($mainSection)
<section class="calidad-main">
    <div class="container">
        <div class="calidad-main__grid">

            {{-- Image --}}
            <div class="calidad-main__image-wrap"
                @if($mainSection->image)
                    style="background-image: url('{{ asset('storage/' . $mainSection->image) }}');"
                @endif>

                @if($certImage?->image)
                    <img
                        class="calidad-main__cert"
                        src="{{ asset('storage/' . $certImage->image) }}"
                        alt="{{ __('ui.calidad.cert_alt') }}"
                        loading="lazy" />
                @endif
            </div>

            {{-- Content --}}
            <div class="calidad-main__content">

                <h2 class="calidad-main__title">
                    {{ calidad_text($mainSection->title, $locale) }}
                </h2>

                <div class="calidad-main__body">
                    {!! calidad_text($mainSection->content, $locale) !!}
                </div>

            </div>

        </div>
    </div>
</section>
@endif


{{-- ════════════════════════════════════════
     DOWNLOADS
════════════════════════════════════════ --}}
@if($downloads->count())
<section class="calidad-downloads">
    <div class="container">

        <h2 class="calidad-downloads__title">{{ __('ui.calidad.downloads') }}</h2>

        <div class="calidad-downloads__grid">
            @foreach($downloads as $dl)
            @php
                $dlName = calidad_text($dl->name, $locale);
                $sizeFormatted = $dl->file_size
                    ? ($dl->file_size < 1048576
                        ? round($dl->file_size / 1024) . ' KB'
                        : number_format($dl->file_size / 1048576, 1) . ' MB')
                    : null;
            @endphp
            <div class="calidad-download-card">

                @if($dl->logo)
                    <div class="calidad-download-card__logo-wrap">
                        <img
                            src="{{ asset('storage/' . $dl->logo) }}"
                            alt="{{ $dlName }}"
                            class="calidad-download-card__logo" />
                    </div>
                @else
                    <div class="calidad-download-card__logo-placeholder">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" stroke="#8895A4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                @endif

                <div class="calidad-download-card__info">
                    <p class="calidad-download-card__name">{{ $dlName }}</p>
                    <p class="calidad-download-card__meta">
                        {{ $dl->file_format ?? 'FILE' }}{{ $sizeFormatted ? ' · ' . $sizeFormatted : '' }}
                    </p>
                </div>

                <a
                    href="{{ asset('storage/' . $dl->file) }}"
                    download="{{ $dlName }}.{{ pathinfo($dl->file, PATHINFO_EXTENSION) }}"
                    class="calidad-download-card__btn"
                    title="Descargar {{ $dlName }}"
                    aria-label="Descargar {{ $dlName }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 15V3M12 15L7 10M12 15L17 10M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="#1F589C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

            </div>
            @endforeach
        </div>

    </div>
</section>
@endif

@endsection

@push('scripts')
<script>
(function () {
    const banner = document.querySelector('.calidad-banner');
    if (!banner) return;
    const slides = banner.querySelectorAll('.calidad-banner__slide');
    const dots   = banner.querySelectorAll('.calidad-banner__dot');
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

/* ── íconos en ítems del body de calidad ── */
(function () {
    const body = document.querySelector('.calidad-main__body');
    if (!body) return;

    const blocks = body.querySelectorAll('p, div, li');
    blocks.forEach(function (el) {
        if (el.querySelector('.calidad-check-icon')) return;

        /* busca primer nodo no vacío */
        let first = el.firstChild;
        while (first && first.nodeType === 3 && first.textContent.trim() === '') {
            first = first.nextSibling;
        }
        if (!first) return;
        const tag = first.nodeName.toUpperCase();
        if (tag !== 'STRONG' && tag !== 'B') return;

        /* envuelve TODO el contenido original en un <span> para que fluya junto */
        const wrapper = document.createElement('span');
        wrapper.style.cssText = 'flex:1;min-width:0;';
        while (el.firstChild) {
            wrapper.appendChild(el.firstChild);
        }

        /* ícono SVG */
        const icon = document.createElement('img');
        icon.src           = '/images/icons/check-circle.svg';
        icon.width         = 21;
        icon.height        = 21;
        icon.alt           = '';
        icon.className     = 'calidad-check-icon';
        icon.style.cssText = 'flex-shrink:0;margin-top:3px;width:21px;height:21px;';

        /* estructura final: [icon] [wrapper con strong + texto] */
        el.appendChild(icon);
        el.appendChild(wrapper);

        el.style.display      = 'flex';
        el.style.alignItems   = 'flex-start';
        el.style.gap          = '7px';
        el.style.marginBottom = '0';
    });

    /* 42px antes del primer ítem */
    const first = body.querySelector('.calidad-check-icon');
    if (first) first.parentElement.style.marginTop = '42px';

    /* 20px entre ítems consecutivos */
    body.querySelectorAll('.calidad-check-icon').forEach(function (ic, i) {
        if (i > 0) ic.parentElement.style.marginTop = '15px';
    });
})();
</script>
@endpush
