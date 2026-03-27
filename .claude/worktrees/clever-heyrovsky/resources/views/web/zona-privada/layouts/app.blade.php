<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', __('ui.zona_privada.price_lists')) – Gran Sasso</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/web.css', 'resources/js/web.js'])
</head>
<body class="zp-body">

{{-- ── HEADER ── --}}
@php $zpClient = auth('client')->user(); @endphp
<header class="navbar navbar--light" id="zpMainNav">
    <div class="nav-container">

        {{-- Logo --}}
        <div class="logo">
            <a href="{{ route('zona-privada.lista-precios') }}">
                <img src="{{ asset('storage/images/logos/logo-color.svg') }}"
                     alt="Gran Sasso"
                     onerror="this.onerror=null;this.src='{{ asset('storage/images/logos/logo.png') }}'">
            </a>
        </div>

        {{-- NAV (desktop: inline · mobile: drawer lateral) --}}
        <nav class="nav-links" id="navLinks">

            {{-- Botón cerrar — solo mobile --}}
            <button class="nav-close" id="navClose" aria-label="Cerrar menú">✕</button>

            <a href="{{ route('zona-privada.lista-precios') }}"
               class="{{ request()->routeIs('zona-privada.lista-precios') ? 'active' : '' }}">
                {{ __('ui.zona_privada.price_lists') }}
            </a>

            <a href="{{ route('zona-privada.info-pagos') }}"
               class="{{ request()->routeIs('zona-privada.info-pagos') ? 'active' : '' }}">
                {{ __('ui.zona_privada.payment_info') }}
            </a>

            <a href="{{ route('zona-privada.novedades') }}"
               class="{{ request()->routeIs('zona-privada.novedades*') ? 'active' : '' }}">
                {{ __('ui.zona_privada.novedades') }}
            </a>

            {{-- Separador | + Selector de idioma --}}
            <div class="nav-lang-group">
                <span class="nav-lang-sep">|</span>
                <div class="nav-lang">
                    <span class="nav-lang__current" tabindex="0" role="button" aria-haspopup="listbox">
                        {{ strtoupper(app()->getLocale()) }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <ul class="nav-lang__dropdown" role="listbox">
                        <li><a href="{{ route('lang.switch', 'es') }}" hreflang="es">ES</a></li>
                        <li><a href="{{ route('lang.switch', 'en') }}" hreflang="en">EN</a></li>
                        <li><a href="{{ route('lang.switch', 'pt') }}" hreflang="pt">PT</a></li>
                    </ul>
                </div>
            </div>

            {{-- Usuario dentro del drawer (solo mobile) --}}
            <div class="zp-nav-user-mobile">
                <span class="zp-nav-user-mobile__avatar">
                    {{ strtoupper(mb_substr($zpClient?->name ?? '', 0, 1)) }}{{ strtoupper(mb_substr($zpClient?->lastname ?? '', 0, 1)) }}
                </span>
                <span class="zp-nav-user-mobile__name">
                    {{ collect(explode(' ', $zpClient?->name ?? ''))->map(fn($w) => ucfirst(strtolower($w)))->implode(' ') }} {{ strtoupper($zpClient?->lastname[0] ?? '') }}.
                </span>
                <form method="POST" action="{{ route('zona-privada.logout') }}"
                      onsubmit="Object.keys(sessionStorage).forEach(function(k){if(k.indexOf('zp_notif_dismissed')===0)sessionStorage.removeItem(k)})">
                    @csrf
                    <button type="submit" class="zp-nav-user-mobile__logout">
                        {{ __('ui.zona_privada.logout') }}
                    </button>
                </form>
            </div>

        </nav>

        {{-- Usuario (desktop) --}}
        <div class="zp-user-desktop" id="zpUserMenu">
            <button id="zpUserBtn"
                    style="display:flex;width:150px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;box-sizing:border-box;border-radius:22px;background:#1F589C;border:none;color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;cursor:pointer;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                {{ collect(explode(' ', $zpClient?->name ?? ''))->map(fn($w) => ucfirst(strtolower($w)))->implode(' ') }} {{ strtoupper($zpClient?->lastname[0] ?? '') }}.
            </button>
            <div id="zpUserDropdown"
                 style="display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border:1px solid #E3E0E0;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.1);min-width:150px;z-index:500;overflow:hidden">
                <form method="POST" action="{{ route('zona-privada.logout') }}"
                      onsubmit="Object.keys(sessionStorage).forEach(function(k){if(k.indexOf('zp_notif_dismissed')===0)sessionStorage.removeItem(k)})">
                    @csrf
                    <button type="submit"
                            style="width:100%;padding:12px 20px;background:none;border:none;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:14px;font-weight:400;text-align:left;cursor:pointer;white-space:nowrap">
                        {{ __('ui.zona_privada.logout') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Overlay mobile --}}
        <div class="nav-overlay" id="navOverlay"></div>

        {{-- Hamburger (mobile) --}}
        <button class="hamburger" id="hamburgerBtn" aria-label="Abrir menú" aria-expanded="false">
            <span class="hamburger__line"></span>
            <span class="hamburger__line"></span>
            <span class="hamburger__line"></span>
        </button>

    </div>
</header>

{{-- ── MAIN ── --}}
<main class="zp-main" style="background:#fff">
    @yield('content')
</main>

@include('web.layouts.footer')

{{-- ── NOTIFICATION POPUP ── --}}
@if(isset($notification) && $notification)
<div id="zp-popup-overlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9999;background:rgba(0,0,0,0.45);justify-content:center;align-items:center">
    <div style="position:relative;width:602px;height:324px;border-radius:10px;background:#FFF;padding:78px 44px 75px 43px;box-sizing:border-box;text-align:center">
        {{-- Close --}}
        <button id="zpPopupClose" style="position:absolute;top:23px;right:23px;background:none;border:none;cursor:pointer;padding:0;line-height:0">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M15 9L9 15M9 9L15 15M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
        {{-- Title --}}
        <h3 style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:24px;font-weight:700;line-height:normal;margin:0 0 31px">{{ $notification->title }}</h3>
        {{-- Body --}}
        <div style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;margin:0 0 39px">{!! nl2br(e($notification->body)) !!}</div>
        {{-- Button --}}
        <a href="{{ route('zona-privada.info-pagos') }}" onclick="sessionStorage.setItem('zp_notif_dismissed_{{ auth('client')->id() }}','1')" style="display:inline-flex;width:138px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;border-radius:22px;background:#1F589C;color:#FFF;text-align:center;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;text-decoration:none;box-sizing:border-box">
            {{ __('ui.zona_privada.popup_more_info') }}
        </a>
    </div>
</div>
<script>
(function(){
    var userId = '{{ auth("client")->id() }}';
    var key = 'zp_notif_dismissed_' + userId;
    if (!sessionStorage.getItem(key)) {
        document.getElementById('zp-popup-overlay').style.display = 'flex';
    }
    document.getElementById('zpPopupClose').addEventListener('click', function(){
        sessionStorage.setItem(key, '1');
        document.getElementById('zp-popup-overlay').style.display = 'none';
    });
}());
</script>
@endif

{{-- TOAST --}}
@if(session('success') || session('error'))
<div id="web-toast" class="web-toast {{ session('error') ? 'web-toast--error' : 'web-toast--success' }}">
    <span class="web-toast__msg">{{ session('success') ?? session('error') }}</span>
    <button class="web-toast__close" onclick="document.getElementById('web-toast').remove()">✕</button>
</div>
<script>
    setTimeout(function(){
        var t=document.getElementById('web-toast');
        if(t){t.classList.add('web-toast--hide');setTimeout(function(){if(t)t.remove();},400);}
    },4500);
</script>
@endif

{{-- User dropdown (desktop) --}}
<script>
(function(){
    var btn = document.getElementById('zpUserBtn');
    var dd  = document.getElementById('zpUserDropdown');
    if (btn && dd) {
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
        });
        document.addEventListener('click', function(){ dd.style.display = 'none'; });
    }
}());
</script>

@stack('scripts')
</body>
</html>
