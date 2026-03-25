@php
    $isHome = request()->routeIs('home');
    $hasTransparentNav = $isHome || request()->routeIs('empresa') || request()->routeIs('products.index') || request()->routeIs('desarrollos') || request()->routeIs('calidad') || request()->routeIs('contacto');
    $hasLightNav = request()->routeIs('products.brand')
                || request()->routeIs('products.gearbox')
                || request()->routeIs('products.show')
                || request()->routeIs('products.search');
@endphp

<header class="navbar{{ $hasTransparentNav ? ' navbar--home' : '' }}{{ $hasLightNav ? ' navbar--light' : '' }}" id="mainNav">

    <div class="nav-container">

        {{-- LOGO --}}
        <div class="logo">
            <a href="{{ route('home') }}">
                @if($hasLightNav)
                    <img src="{{ asset('storage/images/logos/logo-color.svg') }}"
                         alt="Gran Sasso"
                         onerror="this.onerror=null;this.src='{{ asset('storage/images/logos/logo.png') }}'">
                @else
                    <img src="{{ asset('storage/images/logos/logo.svg') }}"
                         alt="Gran Sasso"
                         onerror="this.onerror=null;this.src='{{ asset('storage/images/logos/logo.png') }}'">
                @endif
            </a>
        </div>

        {{-- HAMBURGER (solo visible en mobile) --}}
        <button class="hamburger" id="hamburgerBtn" aria-label="Abrir menú" aria-expanded="false">
            <span class="hamburger__line"></span>
            <span class="hamburger__line"></span>
            <span class="hamburger__line"></span>
        </button>

        {{-- OVERLAY (fondo oscuro en mobile) --}}
        <div class="nav-overlay" id="navOverlay"></div>

        {{-- NAV --}}
        <nav class="nav-links" id="navLinks">

            <button class="nav-close" id="navClose" aria-label="Cerrar menú">✕</button>

            <a href="{{ route('empresa') }}"
               class="{{ request()->routeIs('empresa') ? 'active' : '' }}">{{ __('ui.nav.empresa') }}</a>

            <a href="{{ route('products.index') }}"
               class="{{ request()->routeIs('products.*') ? 'active' : '' }}">{{ __('ui.nav.products') }}</a>

            <a href="{{ route('desarrollos') }}"
               class="{{ request()->routeIs('desarrollos*') ? 'active' : '' }}">{{ __('ui.nav.special_dev') }}</a>

            <a href="{{ route('calidad') }}"
               class="{{ request()->routeIs('calidad') ? 'active' : '' }}">{{ __('ui.nav.quality') }}</a>

            <a href="{{ route('contacto') }}"
               class="{{ request()->routeIs('contacto') ? 'active' : '' }}">{{ __('ui.nav.contact') }}</a>

            {{-- Separador | + Selector de idioma (gap 14px entre ambos) --}}
            <div class="nav-lang-group">

                <span class="nav-lang-sep">|</span>

                <div class="nav-lang">
                    <span class="nav-lang__current" tabindex="0" role="button" aria-haspopup="listbox">
                        {{ strtoupper(app()->getLocale()) }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 6L8 10L12 6" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <ul class="nav-lang__dropdown" role="listbox">
                        <li><a href="{{ route('lang.switch', 'es') }}" hreflang="es">ES</a></li>
                        <li><a href="{{ route('lang.switch', 'en') }}" hreflang="en">EN</a></li>
                        <li><a href="{{ route('lang.switch', 'pt') }}" hreflang="pt">PT</a></li>
                    </ul>
                </div>

            </div>

            {{-- Zona Privada (solo visible en mobile dentro del menú) --}}
            <a href="#zona-privada-modal" class="btn-zona-privada btn-zona-privada--mobile">
                {{ __('ui.nav.private_zone') }}
            </a>

        </nav>

        {{-- ZONA PRIVADA desktop — 24px gap (margin-left en CSS) --}}
        <a href="#zona-privada-modal" class="btn-zona-privada btn-zona-privada--desktop">
            {{ __('ui.nav.private_zone') }}
        </a>

    </div>

</header>

@if($hasTransparentNav)
<script>
(function(){
    var nav = document.getElementById('mainNav');
    function onScroll(){
        nav.classList.toggle('navbar--scrolled', window.scrollY > 40);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}());
</script>
@endif
