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

    @vite(['resources/css/web.css'])
</head>
<body class="zp-body">

{{-- ── HEADER ── --}}
<header class="zp-header">
    <div class="zp-header__inner">

        {{-- Logo --}}
        <a href="{{ route('zona-privada.lista-precios') }}" class="zp-header__logo">
            <svg width="140" height="36" viewBox="0 0 140 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <text x="0" y="26" font-family="Open Sans, sans-serif" font-size="22" font-weight="700" fill="#136CBA">Gran Sasso</text>
            </svg>
        </a>

        {{-- Nav --}}
        <nav class="zp-nav">
            <a href="{{ route('zona-privada.lista-precios') }}"
               class="zp-nav__link {{ request()->routeIs('zona-privada.lista-precios') ? 'zp-nav__link--active' : '' }}">
                {{ __('ui.zona_privada.price_lists') }}
            </a>
            <a href="{{ route('zona-privada.info-pagos') }}"
               class="zp-nav__link {{ request()->routeIs('zona-privada.info-pagos') ? 'zp-nav__link--active' : '' }}">
                {{ __('ui.zona_privada.payment_info') }}
            </a>
            <a href="{{ route('zona-privada.novedades') }}"
               class="zp-nav__link {{ request()->routeIs('zona-privada.novedades*') ? 'zp-nav__link--active' : '' }}">
                {{ __('ui.zona_privada.novedades') }}
            </a>

            {{-- Language selector --}}
            <div class="zp-nav__lang">
                <span class="zp-nav__lang-current" tabindex="0" role="button">
                    {{ strtoupper(app()->getLocale()) }}
                    <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M3 4.5L6 7.5L9 4.5" stroke="#136CBA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                <ul class="zp-nav__lang-dropdown">
                    <li><a href="{{ route('lang.switch', 'es') }}">ES</a></li>
                    <li><a href="{{ route('lang.switch', 'en') }}">EN</a></li>
                    <li><a href="{{ route('lang.switch', 'pt') }}">PT</a></li>
                </ul>
            </div>
        </nav>

        {{-- User menu --}}
        <div class="zp-header__user">
            @php $client = auth('client')->user(); @endphp
            <span class="zp-header__username">{{ $client?->name }} {{ $client?->lastname }}</span>
            <form method="POST" action="{{ route('zona-privada.logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="zp-header__logout">Cerrar sesión</button>
            </form>
        </div>

    </div>
</header>

{{-- ── MAIN ── --}}
<main class="zp-main">
    @yield('content')
</main>

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

{{-- Language dropdown JS --}}
<script>
(function(){
    var btn = document.querySelector('.zp-nav__lang-current');
    var dd  = document.querySelector('.zp-nav__lang-dropdown');
    if (!btn || !dd) return;
    btn.addEventListener('click', function(e){
        e.stopPropagation();
        dd.classList.toggle('open');
    });
    document.addEventListener('click', function(){ dd.classList.remove('open'); });
}());
</script>

@stack('scripts')
</body>
</html>
