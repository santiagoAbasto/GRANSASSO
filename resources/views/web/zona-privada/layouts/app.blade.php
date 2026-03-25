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

        {{-- NAV --}}
        <nav class="nav-links" id="zpNavLinks">

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

        </nav>

        {{-- Usuario --}}
        <form method="POST" action="{{ route('zona-privada.logout') }}" style="margin-left:24px;flex-shrink:0">
            @csrf
            <button type="submit" class="btn-zona-privada" style="background:#0D2038;border-color:#0D2038;color:#fff;cursor:pointer">
                {{ $zpClient?->name }} {{ $zpClient?->lastname[0] ?? '' }}.
            </button>
        </form>

    </div>
</header>

{{-- ── MAIN ── --}}
<main class="zp-main" style="min-height:calc(100vh - 121px);background:#fff">
    @yield('content')
</main>

@include('web.layouts.footer')

{{-- ── NOTIFICATION POPUP ── --}}
@if(isset($notification) && $notification)
<div id="zp-popup-overlay" class="zp-popup-overlay" style="display:none">
    <div class="zp-popup">
        <button class="zp-popup__close" id="zpPopupClose" aria-label="Cerrar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <h3 class="zp-popup__title">{{ $notification->title }}</h3>
        <div class="zp-popup__body">{!! nl2br(e($notification->body)) !!}</div>
    </div>
</div>
<script>
(function(){
    var notifId = {{ $notification->id }};
    var key = 'zp_notif_dismissed_' + notifId;
    if (! sessionStorage.getItem(key)) {
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


@stack('scripts')
</body>
</html>
