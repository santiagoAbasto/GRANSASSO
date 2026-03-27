<section class="hero-search">
    <div class="hero-search__box">

        <form class="hero-search__form"
              action="{{ route('products.search') }}"
              method="GET"
              id="heroSearchForm">

            {{-- MARCA --}}
            <div class="hero-search__field">
                <label class="hero-search__label" for="hs-marca">{{ __('ui.search.brand') }}</label>
                <div class="hero-search__select-wrap">
                    <select name="marca"
                            id="hs-marca"
                            class="hero-search__select">
                        <option value="">{{ __('ui.search.select_brand') }}</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->slug }}"
                            {{ request('marca') === $brand->slug ? 'selected' : '' }}>
                            {{ $brand->getName() }}
                        </option>
                        @endforeach
                    </select>
                    <svg class="hero-search__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <g opacity="0.6">
                            <path d="M5 7.5L10 12.5L15 7.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                    </svg>
                </div>
            </div>

            {{-- MODELO --}}
            <div class="hero-search__field">
                <label class="hero-search__label" for="hs-modelo">{{ __('ui.search.model') }}</label>
                <div class="hero-search__select-wrap">
                    <select name="modelo"
                            id="hs-modelo"
                            class="hero-search__select hero-search__select--modelo">
                        <option value="">{{ __('ui.search.select_model') }}</option>
                    </select>
                    <svg class="hero-search__chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <g opacity="0.6">
                            <path d="M5 7.5L10 12.5L15 7.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </g>
                    </svg>
                </div>
            </div>

            {{-- CÓDIGO --}}
            <div class="hero-search__field hero-search__field--codigo">
                <label class="hero-search__label" for="hs-codigo">{{ __('ui.search.code') }}</label>
                <input type="text"
                       name="codigo"
                       id="hs-codigo"
                       class="hero-search__input"
                       placeholder="{{ __('ui.search.code_placeholder') }}"
                       value="{{ request('codigo') }}">
            </div>

            {{-- BUSCAR --}}
            <div class="hero-search__btn-wrap">
                <button type="submit" class="hero-search__btn">
                    {{ __('ui.search.button') }}
                </button>
            </div>

        </form>

    </div>
</section>

@push('scripts')
<script>
(function () {

    /* ── Placeholder visual: opacity 0.6 cuando no hay valor seleccionado ── */
    document.querySelectorAll('.hero-search__select').forEach(function (sel) {
        function syncPlaceholder() {
            sel.classList.toggle('is-placeholder', sel.value === '');
        }
        syncPlaceholder();
        sel.addEventListener('change', syncPlaceholder);
    });

    /* ── AJAX: cargar modelos según marca ── */
    var marcaSelect  = document.getElementById('hs-marca');
    var modeloSelect = document.getElementById('hs-modelo');

    if (!marcaSelect || !modeloSelect) return;

    function loadModels(brandSlug, selectedModel) {
        modeloSelect.innerHTML = '<option value="">{{ __('ui.search.select_model') }}</option>';
        modeloSelect.disabled = !brandSlug;
        if (!brandSlug) return;

        fetch('/api/gearboxes?brand=' + encodeURIComponent(brandSlug))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                (data.gearboxes || data).forEach(function (g) {
                    var opt = document.createElement('option');
                    opt.value = g.slug;
                    opt.textContent = g.name;
                    if (g.slug === selectedModel) opt.selected = true;
                    modeloSelect.appendChild(opt);
                });
            })
            .catch(function () {});
    }

    // Inicializar si hay marca preseleccionada (ej. al volver del buscador)
    var initialBrand = marcaSelect.value;
    var initialModel = '{{ request('modelo') }}';
    if (initialBrand) loadModels(initialBrand, initialModel);

    marcaSelect.addEventListener('change', function () {
        loadModels(this.value, '');
    });
}());
</script>
@endpush
