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
            <a href="{{ $quality->button_link ?? '#' }}" class="quality-section__btn">
                {{ $quality->getButtonText() }}
            </a>
            @endif

        </div>

        @php $certImages = $quality->images->sortBy('order'); @endphp

        @if($certImages->isNotEmpty())
        <div class="quality-section__iso">
            @foreach($certImages as $certImg)
            <img
                src="{{ asset('storage/' . $certImg->image) }}"
                alt="Certificación"
                class="quality-section__iso-img">
            @endforeach
        </div>
        @endif

    </div>

</section>

@endif
