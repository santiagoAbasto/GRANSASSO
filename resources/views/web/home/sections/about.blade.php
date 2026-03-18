@php
$about = $page?->sections
    ?->where('type','about')
    ->where('is_active',true)
    ->first();
@endphp

@if($about)

<section class="about-section">

    <div class="about-section__img-wrap">
        <img
            src="{{ asset('storage/' . $about->image) }}"
            alt="{{ $about->getTitle() ?: 'Sobre nosotros' }}"
            class="about-section__img">
    </div>

    <div class="about-section__inner">
        <div class="about-section__content">

            <h2 class="about-section__title">
                {{ $about->getTitle() }}
            </h2>

            @foreach(array_filter(array_map('trim', explode("\n", $about->getContent()))) as $paragraph)
            <p class="about-section__text">{{ $paragraph }}</p>
            @endforeach

            @if($about->getButtonText())
            <a href="{{ $about->button_link ?? '#' }}" class="about-section__btn">
                {{ $about->getButtonText() }}
            </a>
            @endif

        </div>
    </div>

</section>

@endif
