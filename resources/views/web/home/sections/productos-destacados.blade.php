@if($brands->count())

<section class="productos-section">
    <div class="container">

        <div class="productos-section__header">
            <h2 class="productos-section__titulo">{{ __('ui.products.title') }}</h2>
            <a href="{{ route('products.index') }}" class="productos-section__ver-todos">{{ __('ui.products.see_all') }}</a>
        </div>

        <div class="productos-section__grid">
            @foreach($brands as $brand)
            <a href="{{ route('products.brand', $brand->slug) }}" class="brand-card">

                <div class="brand-card__logo">
                    @if($brand->logo)
                        <img src="{{ asset('storage/' . $brand->logo) }}"
                             alt="{{ $brand->getName() }}"
                             loading="lazy">
                    @else
                        <span class="brand-card__name-fallback">{{ $brand->getName() }}</span>
                    @endif
                </div>

                <span class="brand-card__divider"></span>

                <div class="brand-card__footer">
                    <span class="brand-card__compatible">{{ __('ui.products.compatible_with') }}</span>
                    <strong class="brand-card__brand">{{ $brand->getName() }}</strong>
                </div>

            </a>
            @endforeach
        </div>

    </div>
</section>

@endif
