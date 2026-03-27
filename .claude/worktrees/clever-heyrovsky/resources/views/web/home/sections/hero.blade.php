@if($slides->count())

<section class="hero" data-slides="{{ $slides->count() }}">

    {{-- SLIDES BACKGROUND --}}
    @foreach($slides as $index => $slide)
    @if($slide->media_type === 'video' && $slide->video_url)
        @php
            $embedUrl = null;
            $url = $slide->video_url;
            if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $m)) {
                $embedUrl = 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&mute=1&loop=1&controls=0&showinfo=0&rel=0&playlist=' . $m[1];
            }
        @endphp
        @if($embedUrl)
        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
             data-index="{{ $index }}"
             style="transform: none;">
            <iframe
                src="{{ $index === 0 ? $embedUrl : '' }}"
                data-src="{{ $embedUrl }}"
                title="Video slide {{ $index + 1 }}"
                style="width:100%; height:100%; border:none; position:absolute; inset:0; object-fit:cover; pointer-events:none;"
                allow="autoplay; encrypted-media"
                allowfullscreen
            ></iframe>
        </div>
        @else
        <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
             data-index="{{ $index }}"
             style="background-image: url('{{ $slide->media_url }}');">
        </div>
        @endif
    @else
    <div class="hero-slide {{ $index === 0 ? 'active' : '' }}"
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
                <a href="{{ $slide->button_link }}" class="hero-btn">
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
