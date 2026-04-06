@extends('web.layouts.app')

@section('title', __('ui.contacto.page_title'))

@section('content')

{{-- ═══════════════════════════════════
     BANNER
═══════════════════════════════════ --}}
<section class="contacto-banner" data-slides="{{ $bannerSlides->count() }}">

    @if($bannerSlides->count())
        @foreach($bannerSlides as $i => $slide)
        <div class="contacto-banner__slide {{ $i === 0 ? 'active' : '' }}"
             @if($slide->image)
                 style="background: url('{{ asset('storage/' . $slide->image) }}') lightgray 50% / cover no-repeat;"
             @endif>
        </div>
        @endforeach
    @else
        <div class="contacto-banner__slide active" style="background-color:#0D2038;"></div>
    @endif

    <div class="contacto-banner__overlay"></div>

    <div class="contacto-banner__inner">

        <div class="contacto-banner__topbar">
            <nav class="contacto-banner__breadcrumb" aria-label="{{ __('ui.contacto.bc_aria') }}">
                <a href="{{ route('home') }}" class="contacto-banner__bc-home">{{ __('ui.breadcrumb.home') }}</a>
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" aria-hidden="true" style="flex-shrink:0;">
                    <path d="M4.5 9L7.5 6L4.5 3" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="contacto-banner__bc-current">{{ __('ui.contacto.page_title') }}</span>
            </nav>
        </div>

        <div class="contacto-banner__bottom">
            <h1 class="contacto-banner__title">{{ __('ui.contacto.page_title') }}</h1>

            @if($bannerSlides->count() >= 2)
            <div class="contacto-banner__dots">
                @foreach($bannerSlides as $i => $slide)
                <button class="contacto-banner__dot {{ $i === 0 ? 'active' : '' }}"
                        data-index="{{ $i }}"
                        aria-label="{{ __('ui.contacto.slide_aria', ['num' => $i + 1]) }}"></button>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</section>


{{-- ═══════════════════════════════════
     CONTACT SECTION
═══════════════════════════════════ --}}
<section class="contact-page">
    <div class="container">

        <div class="contact-grid">

            {{-- COL 1: INFO --}}
            <div class="contact-info">

                <p class="contact-info__text">{{ __('ui.contacto.intro') }}</p>

                <div class="contact-info__items">

                    @if($contactInfo['address'])
                    <a href="https://maps.app.goo.gl/LnddZUSVNWWmiaVC7" target="_blank" rel="noopener noreferrer" class="contact-info__row" style="text-decoration:none;">
                        <span class="contact-info__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M20 10C20 14.993 14.461 20.193 12.601 21.799C12.4277 21.9293 12.2168 21.9998 12 21.9998C11.7832 21.9998 11.5723 21.9293 11.399 21.799C9.539 20.193 4 14.993 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z" stroke="#1F589C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="#1F589C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="contact-info__label">{!! nl2br(e($contactInfo['address'])) !!}</span>
                    </a>
                    @endif

                    @if(!empty($contactInfo['phones']))
                    <div class="contact-info__row">
                        <span class="contact-info__icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M13.832 16.568C14.0385 16.6628 14.2712 16.6845 14.4917 16.6294C14.7122 16.5744 14.9073 16.4458 15.045 16.265L15.4 15.8C15.5863 15.5516 15.8279 15.35 16.1056 15.2111C16.3833 15.0723 16.6895 15 17 15H20C20.5304 15 21.0391 15.2107 21.4142 15.5858C21.7893 15.9609 22 16.4696 22 17V20C22 20.5304 21.7893 21.0391 21.4142 21.4142C21.0391 21.7893 20.5304 22 20 22C15.2261 22 10.6477 20.1036 7.27208 16.7279C3.89642 13.3523 2 8.7739 2 4C2 3.46957 2.21071 2.96086 2.58579 2.58579C2.96086 2.21071 3.46957 2 4 2H7C7.53043 2 8.03914 2.21071 8.41421 2.58579C8.78929 2.96086 9 3.46957 9 4V7C9 7.31049 8.92771 7.61672 8.78885 7.89443C8.65 8.17214 8.44839 8.41371 8.2 8.6L7.732 8.951C7.54842 9.09118 7.41902 9.29059 7.36579 9.51535C7.31256 9.74012 7.33878 9.97638 7.44 10.184C8.80668 12.9599 11.0544 15.2048 13.832 16.568Z" stroke="#1F589C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="contact-info__label">{{ implode(' | ', $contactInfo['phones']) }}</span>
                    </div>
                    @endif

                    @if(!empty($contactInfo['emails']))
                    <div class="contact-info__col-items">
                        <span class="contact-info__icon" style="margin-top:2px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M22 7L13.009 12.727C12.7039 12.9042 12.3573 12.9976 12.0045 12.9976C11.6517 12.9976 11.3051 12.9042 11 12.727L2 7M4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6C2 4.89543 2.89543 4 4 4Z" stroke="#1F589C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div class="contact-info__email-list">
                            @foreach($contactInfo['emails'] as $email)
                            <a href="mailto:{{ $email }}" class="contact-info__label contact-info__label--link">{{ $email }}</a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>

            </div>

            {{-- COL 2+3: FORM --}}
            <form class="contact-form" action="{{ route('contact.store') }}" method="POST">
                @csrf

                @if(session('success'))
                <div class="contact-form__success" style="grid-column:1/-1;">{{ __('ui.contacto.form_success') }}</div>
                @endif

                <div class="contact-form__inner">

                    <div class="contact-form__col">
                        <div class="contact-field">
                            <label class="contact-field__label" for="name">{{ __('ui.contacto.form_name') }} *</label>
                            <input type="text" id="name" name="name" class="contact-field__input"
                                   value="{{ old('name') }}" required>
                            @error('name')<span class="contact-field__error">{{ $message }}</span>@enderror
                        </div>

                        <div class="contact-field">
                            <label class="contact-field__label" for="email">{{ __('ui.contacto.form_email') }} *</label>
                            <input type="email" id="email" name="email" class="contact-field__input"
                                   value="{{ old('email') }}" required>
                            @error('email')<span class="contact-field__error">{{ $message }}</span>@enderror
                        </div>

                        <div class="contact-field contact-field--textarea">
                            <label class="contact-field__label" for="message">{{ __('ui.contacto.form_message') }}</label>
                            <textarea id="message" name="message" class="contact-field__textarea">{{ old('message') }}</textarea>
                            @error('message')<span class="contact-field__error">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="contact-form__col contact-form__col--right">
                        <div class="contact-field">
                            <label class="contact-field__label" for="phone">{{ __('ui.contacto.form_phone') }} *</label>
                            <input type="tel" id="phone" name="phone" class="contact-field__input"
                                   value="{{ old('phone') }}" required>
                            @error('phone')<span class="contact-field__error">{{ $message }}</span>@enderror
                        </div>

                        <div class="contact-field">
                            <label class="contact-field__label" for="company">{{ __('ui.contacto.form_company') }}</label>
                            <input type="text" id="company" name="company" class="contact-field__input"
                                   value="{{ old('company') }}">
                        </div>

                        <div class="contact-form__submit-area">
                            <span class="contact-form__required">{{ __('ui.contacto.form_required') }}</span>
                            <button type="submit" class="contact-form__btn">{{ __('ui.contacto.form_submit') }}</button>
                        </div>
                    </div>

                </div>
            </form>

        </div>

    </div>
</section>


{{-- ═══════════════════════════════════
     MAPA
═══════════════════════════════════ --}}
<section class="contacto-map">
    <div class="contacto-map__inner">
        @if(!empty($contactInfo['map_embed']))
            {!! $contactInfo['map_embed'] !!}
        @else
            <a href="https://maps.app.goo.gl/LnddZUSVNWWmiaVC7"
               target="_blank"
               rel="noopener noreferrer"
               class="contacto-map__link">
                <iframe
                    src="https://maps.google.com/maps?q=Calle+33+Nro+2235,+Villa+Maipú,+San+Martín,+Buenos+Aires,+Argentina&z=16&output=embed"
                    width="100%"
                    height="100%"
                    style="border:0;pointer-events:none;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Gran Sasso – Ubicación">
                </iframe>
            </a>
        @endif
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    const banner = document.querySelector('.contacto-banner');
    if (!banner) return;
    const slides = banner.querySelectorAll('.contacto-banner__slide');
    const dots   = banner.querySelectorAll('.contacto-banner__dot');
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
    function startTimer() { timer = setInterval(() => goTo(current + 1), 5000); }
    dots.forEach((dot, i) => dot.addEventListener('click', () => { clearInterval(timer); goTo(i); startTimer(); }));
    startTimer();
})();
</script>
@endpush
