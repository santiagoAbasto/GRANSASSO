@if($slides->count())

<section class="hero" data-slides="{{ $slides->count() }}">

    {{-- SLIDES BACKGROUND --}}
    @foreach($slides as $index => $slide)
    @if($slide->media_type === 'youtube' && $slide->youtube_embed_url)
        <div class="hero-slide hero-slide--youtube {{ $index === 0 ? 'active' : '' }}"
             data-index="{{ $index }}">
            <iframe
                src="{{ $index === 0 ? $slide->youtube_embed_url : '' }}"
                data-src="{{ $slide->youtube_embed_url }}"
                class="hero-slide__iframe"
                title="Video slide {{ $index + 1 }}"
                allow="autoplay; encrypted-media"
                allowfullscreen
            ></iframe>
        </div>
    @elseif($slide->media_type === 'video' && $slide->media_url)
        <div class="hero-slide hero-slide--video {{ $index === 0 ? 'active' : '' }}"
             data-index="{{ $index }}">
            <video
                class="hero-slide__video"
                src="{{ $slide->media_url }}"
                {{ $index === 0 ? 'autoplay' : '' }}
                muted
                loop
                playsinline
                preload="metadata"
            ></video>
        </div>
    @else
    <div class="hero-slide hero-slide--image {{ $index === 0 ? 'active' : '' }}"
         data-index="{{ $index }}"
         style="background-image: url('{{ $slide->media_url }}');">
    </div>
    @endif
    @endforeach

    {{-- OVERLAY OSCURO --}}
    <div class="hero-overlay"></div>

    {{-- CONTENIDO — uno por slide, se muestra/oculta con JS --}}
    <div class="container">
        <div class="hero-inner">

            @foreach($slides as $index => $slide)
            <div class="hero-content {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">

                @if($slide->getTitle())
                <h1 class="hero-title">
                    {!! nl2br(e($slide->getTitle())) !!}
                </h1>
                @endif

                @if($slide->getSubtitle())
                <p class="hero-subtitle">{{ $slide->getSubtitle() }}</p>
                @endif

                @if($slide->getButtonText())
                <a href="{{ $slide->safe_button_link ?? '#' }}" class="hero-btn">
                    {{ $slide->getButtonText() }}
                </a>
                @endif

            </div>
            @endforeach

            {{-- SLIDER INDICATOR — solo visible cuando hay más de 1 slide --}}
            @if($slides->count() > 1)
            <div class="hero-slider-dots">
                @foreach($slides as $index => $slide)
                <button
                    class="hero-dot {{ $index === 0 ? 'active' : '' }}"
                    data-index="{{ $index }}"
                    aria-label="Ir al slide {{ $index + 1 }}"
                ></button>
                @endforeach
            </div>
            @endif

        </div>
    </div>

</section>

@endif
