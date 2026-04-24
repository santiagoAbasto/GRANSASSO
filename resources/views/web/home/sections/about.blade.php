@php
$about = $page?->sections
    ?->where('type','about')
    ->where('is_active',true)
    ->first();
@endphp

@if($about)

<section class="about-section">
    <div class="about-section__inner">
        <div class="about-section__img-wrap">
            <img
                src="{{ asset('storage/' . $about->image) }}"
                alt="{{ $about->getTitle() ?: 'Sobre nosotros' }}"
                class="about-section__img">
        </div>

        <div class="about-section__content">

            <h2 class="about-section__title">
                {{ $about->getTitle() }}
            </h2>

            @if($about->getSubtitle())
            <div class="about-section__text about-section__text--bold">{{ $about->getSubtitle() }}</div>
            @endif

            @php
                $content = $about->getContent();
                if ($content && !preg_match('/<[^>]+>/', $content)) {
                    $content = nl2br(e($content));
                }
            @endphp
            <div class="about-section__text">{!! $content !!}</div>

            @if($about->getButtonText())
            <a href="{{ $about->safe_button_link ?? '#' }}" class="about-section__btn">
                {{ $about->getButtonText() }}
            </a>
            @endif

        </div>
    </div>

</section>

@endif
