@if($posts->count())

@php
    $postGroups = $posts->values()->chunk(3);
    $totalGroups = $postGroups->count();
@endphp

<section class="novedades">
    <div class="container">

        {{-- HEADER --}}
        <div class="novedades__header">
            <h2 class="novedades__title">Novedades</h2>
            <a href="{{ route('novedades') }}" class="novedades__ver-todas">Ver todas</a>
        </div>

        {{-- SLIDER WRAPPER --}}
        <div class="novedades__slider">

            @foreach($postGroups as $groupIndex => $group)
            <div class="novedades__slide {{ $groupIndex === 0 ? 'active' : '' }}" data-slide="{{ $groupIndex }}">
                <div class="novedades__grid">

                    @foreach($group as $post)
                    <a href="{{ route('novedades.show', $post) }}" class="news-card">

                        {{-- IMAGEN: --cover para fotos reales (JPG/JPEG), --contain para logos y docs (PNG, etc.) --}}
                        @php
                            $urlPath  = parse_url($post->image_url ?? '', PHP_URL_PATH) ?? '';
                            $ext      = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION));
                            $isCover  = in_array($ext, ['jpg', 'jpeg', 'webp']);
                        @endphp
                        <div class="news-card__img-wrap {{ $isCover ? 'news-card__img-wrap--cover' : '' }}">
                            <img
                                src="{{ $post->image_url }}"
                                alt="{{ e($post->title) }}"
                                class="news-card__img"
                                loading="lazy">
                        </div>

                        {{-- BODY --}}
                        <div class="news-card__body">

                            @php $cat = $post->getLocalizedCategory(); @endphp
                            @if($cat)
                            <span class="news-card__category">{{ strtoupper($cat) }}</span>
                            @endif

                            <h3 class="news-card__title">{{ $post->title }}</h3>

                            @if($post->excerpt)
                            <p class="news-card__excerpt">{{ $post->excerpt }}</p>
                            @endif

                            <div class="news-card__more">
                                <span class="news-card__more-text">Leer más</span>
                                <span class="news-card__arrow" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="#546F5D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>

                        </div>

                    </a>
                    @endforeach

                </div>
            </div>
            @endforeach

        </div>

        {{-- DOTS: se muestran si hay más de 1 grupo de 3 --}}
        @if($totalGroups > 1)
        <div class="novedades__dots">
            @foreach($postGroups as $i => $group)
            <button class="novedades__dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" aria-label="Página {{ $i + 1 }}"></button>
            @endforeach
        </div>
        @endif

    </div>
</section>

@endif
