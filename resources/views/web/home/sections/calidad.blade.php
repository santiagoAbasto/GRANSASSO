@php
$quality = $page?->sections
    ?->where('type', 'quality')
    ->where('is_active', true)
    ->first();
@endphp

@if($quality)

<section
    class="quality-section"
    style="background-image: url('{{ asset('storage/' . $quality->image) }}')">

    <div class="quality-section__inner">

        <div class="quality-section__content">

            <h2 class="quality-section__title">
                {{ $quality->getTitle() }}
            </h2>

            <div class="quality-section__text">
                {!! $quality->getContent() !!}
            </div>

            @if($quality->getButtonText())
            <a href="{{ $quality->safe_button_link ?? '#' }}" class="quality-section__btn">
                {{ $quality->getButtonText() }}
            </a>
            @endif

        </div>

        @php $certIcon = $quality->images->sortBy('order')->first(); @endphp

        @if($certIcon)
        <div class="quality-section__iso">
            <img
                src="{{ asset('storage/' . $certIcon->image) }}"
                alt="Certificación"
                class="quality-section__iso-img">
        </div>
        @endif

    </div>

</section>

@endif
