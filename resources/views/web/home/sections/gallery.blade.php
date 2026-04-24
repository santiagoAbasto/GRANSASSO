@php
$gallerySection = $page?->sections->where('type', 'home_gallery')->where('is_active', true)->first();
$galleryImages = $gallerySection?->images?->sortBy('order')->take(3) ?? collect();
@endphp

@if($galleryImages->count())
<section class="home-gallery">
    <div class="container">
        <div class="home-gallery__grid">
            @foreach($galleryImages as $image)
            <figure class="home-gallery__item">
                <img
                    src="{{ asset('storage/' . $image->image) }}"
                    alt="Imagen destacada de inicio"
                    class="home-gallery__img"
                    loading="lazy">
            </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
