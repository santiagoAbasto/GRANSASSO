@extends('web.layouts.app')

@section('title', __('ui.desarrollos.page_title'))

@section('content')

@php
$bannerSection  = $page?->sections->where('type', 'banner')->first();
$bannerSlides   = $bannerSection?->images->sortBy('order') ?? collect();
$mainSection    = $page?->sections->where('type', 'desarrollos_main')->where('is_active', true)->first();
$gallerySection = $page?->sections->where('type', 'desarrollos_gallery')->first();
$galleryImages  = $gallerySection?->images->sortBy('order') ?? collect();

$formatDesarrollosContent = static function ($content): string {
    $content = trim((string) $content);

    if ($content === '') {
        return '';
    }

    if (preg_match('/<[^>]+>/', $content)) {
        return $content;
    }

    $paragraphs = preg_split("/(?:\r?\n){2,}/", $content) ?: [];
    $paragraphs = array_values(array_filter(array_map('trim', $paragraphs), static fn ($paragraph) => $paragraph !== ''));

    if (count($paragraphs) > 1) {
        return implode('', array_map(static fn ($paragraph) => '<p>' . nl2br(e($paragraph)) . '</p>', $paragraphs));
    }

    return '<p>' . nl2br(e($content)) . '</p>';
};
@endphp


{{-- ════════════════════════════════════════
     BANNER
════════════════════════════════════════ --}}
<section class="desarrollos-banner" data-slides="{{ $bannerSlides->count() }}">

    {{-- SLIDES --}}
    @if($bannerSlides->count())
        @foreach($bannerSlides as $i => $slide)
        @php
            $ytId = null;
            if ($slide->video_url) {
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $slide->video_url, $ytMatch);
                $ytId = $ytMatch[1] ?? null;
            }
            $embedUrl = $ytId
                ? 'https://www.youtube.com/embed/' . $ytId . '?autoplay=1&mute=1&controls=0&loop=1&playlist=' . $ytId . '&playsinline=1&rel=0'
                : null;
        @endphp

        <div class="desarrollos-banner__slide {{ $i === 0 ? 'active' : '' }}"
             data-index="{{ $i }}"
             @if(!$ytId && $slide->image)
                 style="background-image: url('{{ asset('storage/' . $slide->image) }}')"
             @endif>

            @if($embedUrl)
            <iframe
                class="desarrollos-banner__video"
                src="{{ $i === 0 ? $embedUrl : '' }}"
                data-src="{{ $embedUrl }}"
                title="Video slide {{ $i + 1 }}"
                allow="autoplay; encrypted-media"
                allowfullscreen
                frameborder="0"></iframe>
            @endif

        </div>
        @endforeach
    @else
        <div class="desarrollos-banner__slide active" style="background-color:#0D2038"></div>
    @endif

    {{-- OVERLAYS --}}
    <div class="desarrollos-banner__overlay-dark"></div>
    <div class="desarrollos-banner__overlay"></div>

    {{-- INNER --}}
    <div class="desarrollos-banner__inner">

        {{-- BREADCRUMB --}}
        <div class="desarrollos-banner__topbar">
            <nav class="desarrollos-banner__breadcrumb" aria-label="{{ __('ui.desarrollos.page_title') }}">
                <a href="{{ route('home') }}" class="desarrollos-banner__bc-home">{{ __('ui.breadcrumb.home') }}</a>
                <svg class="desarrollos-banner__bc-sep" width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="aspect-ratio:1/1;flex-shrink:0;">
                    <path d="M4.5 9L7.5 6L4.5 3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="desarrollos-banner__bc-current">{{ __('ui.desarrollos.page_title') }}</span>
            </nav>
        </div>

        {{-- TITLE + DOTS --}}
        <div class="desarrollos-banner__bottom">
            <h1 class="desarrollos-banner__title">{{ __('ui.desarrollos.page_title') }}</h1>

            @if($bannerSlides->count() >= 2)
            <div class="desarrollos-banner__dots">
                @foreach($bannerSlides as $i => $slide)
                <button
                    class="desarrollos-banner__dot {{ $i === 0 ? 'active' : '' }}"
                    data-index="{{ $i }}"
                    aria-label="Ir al slide {{ $i + 1 }}"></button>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</section>


{{-- ════════════════════════════════════════
     MAIN CONTENT + FORM
════════════════════════════════════════ --}}
<section class="desarrollos-main">
    <div class="container">
        <div class="desarrollos-main__grid">

            {{-- LEFT: text content --}}
            <div class="desarrollos-main__content">

                <h2 class="desarrollos-main__title">
                    {{ $mainSection?->getTitle() ?: __('ui.desarrollos.section_title') }}
                </h2>

                <div class="desarrollos-main__body">
                    {!! $mainSection ? $formatDesarrollosContent($mainSection->getContent()) : implode('', [
                        '<p>' . e(__('ui.desarrollos.p1')) . '</p>',
                        '<p>' . e(__('ui.desarrollos.p2')) . '</p>',
                        '<p>' . e(__('ui.desarrollos.p3')) . '</p>',
                        '<p>' . e(__('ui.desarrollos.p4')) . '</p>',
                        '<p>' . e(__('ui.desarrollos.p5')) . '</p>',
                        '<p>' . e(__('ui.desarrollos.p6')) . '</p>',
                    ]) !!}
                </div>

            </div>

            {{-- RIGHT: contact form --}}
            <div class="desarrollos-main__form-col">

                @if(session('success'))
                <div class="desarrollos-form__success">
                    {{ session('success') }}
                </div>
                @endif

                <form
                    class="desarrollos-form"
                    action="{{ route('desarrollos.store') }}"
                    method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="desarrollos-form__field">
                        <label class="desarrollos-form__label" for="dev-name">{{ __('ui.desarrollos.form_name') }} <span class="desarrollos-form__req">*</span></label>
                        <input
                            type="text"
                            id="dev-name"
                            name="name"
                            class="desarrollos-form__input {{ $errors->has('name') ? 'is-error' : '' }}"
                            value="{{ old('name') }}"
                            required>
                        @error('name')<span class="desarrollos-form__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="desarrollos-form__field">
                        <label class="desarrollos-form__label" for="dev-phone">{{ __('ui.desarrollos.form_phone') }} <span class="desarrollos-form__req">*</span></label>
                        <input
                            type="tel"
                            id="dev-phone"
                            name="phone"
                            class="desarrollos-form__input {{ $errors->has('phone') ? 'is-error' : '' }}"
                            value="{{ old('phone') }}"
                            required>
                        @error('phone')<span class="desarrollos-form__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="desarrollos-form__field">
                        <label class="desarrollos-form__label" for="dev-email">{{ __('ui.desarrollos.form_email') }} <span class="desarrollos-form__req">*</span></label>
                        <input
                            type="email"
                            id="dev-email"
                            name="email"
                            class="desarrollos-form__input {{ $errors->has('email') ? 'is-error' : '' }}"
                            value="{{ old('email') }}"
                            required>
                        @error('email')<span class="desarrollos-form__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="desarrollos-form__field">
                        <label class="desarrollos-form__label" for="dev-file">{{ __('ui.desarrollos.form_file') }}</label>
                        <label class="desarrollos-form__file-label" for="dev-file">
                            <span class="desarrollos-form__file-text" id="dev-file-text">{{ __('ui.desarrollos.form_file_ph') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style="width: 20px; height: 20px; aspect-ratio: 1/1; opacity: 0.5;">
                                <g opacity="0.5">
                                    <path d="M10 2.5V12.5M10 2.5L14.1667 6.66667M10 2.5L5.83333 6.66667M17.5 12.5V15.8333C17.5 16.2754 17.3244 16.6993 17.0118 17.0118C16.6993 17.3244 16.2754 17.5 15.8333 17.5H4.16667C3.72464 17.5 3.30072 17.3244 2.98816 17.0118C2.67559 16.6993 2.5 16.2754 2.5 15.8333V12.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                            </svg>
                        </label>
                        <input
                            type="file"
                            id="dev-file"
                            name="file"
                            class="desarrollos-form__file-input"
                            accept=".pdf,.doc,.docx,.dwg,.jpg,.jpeg,.png">
                        @error('file')<span class="desarrollos-form__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="desarrollos-form__field desarrollos-form__field--textarea">
                        <label class="desarrollos-form__label" for="dev-message">{{ __('ui.desarrollos.form_message') }} <span class="desarrollos-form__req">*</span></label>
                        <textarea
                            id="dev-message"
                            name="message"
                            class="desarrollos-form__textarea {{ $errors->has('message') ? 'is-error' : '' }}"
                            required>{{ old('message') }}</textarea>
                        @error('message')<span class="desarrollos-form__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="desarrollos-form__footer">
                        <span class="desarrollos-form__required-note">{{ __('ui.desarrollos.form_required') }}</span>
                        <button type="submit" class="desarrollos-form__btn">{{ __('ui.desarrollos.form_submit') }}</button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</section>


{{-- ════════════════════════════════════════
     TRABAJOS REALIZADOS
════════════════════════════════════════ --}}
@if($galleryImages->count())
<section class="desarrollos-gallery">
    <div class="container">
        <h2 class="desarrollos-gallery__title">{{ __('ui.desarrollos.gallery_title') }}</h2>
        <div class="desarrollos-gallery__grid">
            @foreach($galleryImages as $img)
            <button
                type="button"
                class="desarrollos-gallery__item"
                data-gallery-trigger
                data-full="{{ asset('storage/' . $img->image) }}"
                data-alt="{{ __('ui.desarrollos.gallery_title') }}"
                aria-label="Ampliar imagen de la galería">
                <img
                    src="{{ asset('storage/' . $img->image) }}"
                    alt="{{ __('ui.desarrollos.gallery_title') }}"
                    class="desarrollos-gallery__img"
                    loading="lazy">
            </button>
            @endforeach
        </div>
    </div>
</section>

<div class="desarrollos-lightbox" data-gallery-modal hidden>
    <div class="desarrollos-lightbox__backdrop" data-gallery-close></div>
    <div class="desarrollos-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Imagen ampliada">
        <button type="button" class="desarrollos-lightbox__close" data-gallery-close aria-label="Cerrar imagen ampliada">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
        <img src="" alt="" class="desarrollos-lightbox__img" data-gallery-modal-image>
    </div>
</div>
@endif


@push('scripts')
<script>
(function () {
    var fileInput = document.getElementById('dev-file');
    var fileText  = document.getElementById('dev-file-text');
    if (fileInput && fileText) {
        fileInput.addEventListener('change', function () {
            fileText.textContent = this.files.length > 0 ? this.files[0].name : '{{ __('ui.desarrollos.form_file_ph') }}';
        });
    }
}());

(function () {
    const banner = document.querySelector('.desarrollos-banner');
    if (!banner) return;
    const slides = banner.querySelectorAll('.desarrollos-banner__slide');
    const dots   = banner.querySelectorAll('.desarrollos-banner__dot');
    if (slides.length <= 1) return;
    let current = 0;
    let timer   = null;
    function goTo(index) {
        slides[current].classList.remove('active');
        if (dots[current]) dots[current].classList.remove('active');
        current = (index + slides.length) % slides.length;
        slides[current].classList.add('active');
        if (dots[current]) dots[current].classList.add('active');
    }
    function startTimer() { timer = setInterval(() => goTo(current + 1), 5000); }
    dots.forEach(function (dot, i) {
        dot.addEventListener('click', function () {
            clearInterval(timer);
            goTo(i);
            startTimer();
        });
    });
    startTimer();
}());

(function () {
    const modal = document.querySelector('[data-gallery-modal]');
    const modalImage = document.querySelector('[data-gallery-modal-image]');
    const triggers = document.querySelectorAll('[data-gallery-trigger]');
    const closers = document.querySelectorAll('[data-gallery-close]');

    if (!modal || !modalImage || triggers.length === 0) return;

    function closeModal() {
        modal.hidden = true;
        document.body.classList.remove('desarrollos-lightbox-open');
        modalImage.src = '';
        modalImage.alt = '';
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            modalImage.src = trigger.getAttribute('data-full') || '';
            modalImage.alt = trigger.getAttribute('data-alt') || '';
            modal.hidden = false;
            document.body.classList.add('desarrollos-lightbox-open');
        });
    });

    closers.forEach(function (closer) {
        closer.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.hidden) {
            closeModal();
        }
    });
}());
</script>
@endpush

@endsection
