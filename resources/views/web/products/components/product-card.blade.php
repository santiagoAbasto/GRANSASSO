<a href="{{ route('products.show',$product->slug) }}" class="product-card" style="--cat-color: {{ $product->category->color ?? '#28597D' }}">

    <div class="product-card-image">

        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">

        <div class="product-card-overlay">
            <span class="product-card-plus">+</span>
        </div>

    </div>

    <div class="product-card-body">

        <span class="product-category">
            {{ strtoupper($product->category->name ?? '') }}
        </span>

        <h3 class="product-title">
            {{ $product->name }}
        </h3>

        <p class="product-subtitle">
            {{ $product->short_description }}
        </p>

    </div>

</a>