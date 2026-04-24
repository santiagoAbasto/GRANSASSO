<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

@php
use App\Models\SeoMeta;

// ── Detectar página y cargar meta desde DB ──────────────────────────────
$_route   = Route::currentRouteName() ?? '';
$_pageKey = match(true) {
    $_route === 'home'                          => 'home',
    str_starts_with($_route, 'products')        => 'productos',
    $_route === 'empresa'                       => 'empresa',
    $_route === 'calidad'                       => 'calidad',
    str_starts_with($_route, 'novedades')       => 'novedades',
    str_starts_with($_route, 'contacto')        => 'contacto',
    default                                     => null,
};
$_seo        = $_pageKey ? SeoMeta::where('page', $_pageKey)->first() : null;
$_pageKeywords = $__env->hasSection('meta_keywords') ? trim($__env->yieldContent('meta_keywords')) : null;
$_pageOgImage  = $__env->hasSection('meta_image') ? trim($__env->yieldContent('meta_image')) : null;
$_pageCanonical = $__env->hasSection('canonical_url') ? trim($__env->yieldContent('canonical_url')) : url()->current();
$_pageOgType = $__env->hasSection('og_type') ? trim($__env->yieldContent('og_type')) : 'website';
$_articlePublished = $__env->hasSection('article_published_time') ? trim($__env->yieldContent('article_published_time')) : null;

$_seoKeywords = $_pageKeywords ?: ($_seo?->keywords ?: '');
$_seoOgImage  = $_pageOgImage ?: ($_seo?->og_image_url ?: asset('images/og/gran-sasso-default.png'));

// ── Título final: página específica tiene prioridad sobre el del admin ──
$_pageTitle  = $__env->hasSection('title') ? trim($__env->yieldContent('title')) : null;
$_adminTitle = $_seo?->title ?: null;
$_metaTitle  = $_pageTitle
    ? $_pageTitle . ' – Gran Sasso'
    : ($_adminTitle ?: 'Gran Sasso – Engranajes y Transmisión Industrial');

// ── Descripción final ───────────────────────────────────────────────────
$_pageDesc  = $__env->hasSection('meta_description') ? trim($__env->yieldContent('meta_description')) : null;
$_metaDesc  = $_pageDesc
    ?: ($_seo?->description ?: 'Gran Sasso – Fabricación de engranajes y partes de transmisión para cajas de velocidades.');
@endphp

<title>{{ $_metaTitle }}</title>

<meta name="description" content="{{ $_metaDesc }}">
<link rel="canonical" href="{{ $_pageCanonical }}">

@if(!empty($_seoKeywords))
<meta name="keywords" content="{{ $_seoKeywords }}">
@endif

{{-- ── Open Graph (WhatsApp, LinkedIn, Facebook) ── --}}
<meta property="og:type"        content="{{ $_pageOgType }}">
<meta property="og:site_name"   content="Gran Sasso">
<meta property="og:url"         content="{{ $_pageCanonical }}">
<meta property="og:title"       content="{{ $_metaTitle }}">
<meta property="og:description" content="{{ $_metaDesc }}">
@if($_seoOgImage)
<meta property="og:image"        content="{{ $_seoOgImage }}">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
@endif
@if($_articlePublished && $_pageOgType === 'article')
<meta property="article:published_time" content="{{ $_articlePublished }}">
@endif

{{-- ── Twitter Card ── --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $_metaTitle }}">
<meta name="twitter:description" content="{{ $_metaDesc }}">
@if($_seoOgImage)
<meta name="twitter:image"       content="{{ $_seoOgImage }}">
@endif

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- ── Suave transición de páginas (View Transitions API) ── --}}
<meta name="view-transition" content="same-origin">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

@vite(['resources/css/web.css','resources/js/web.js'])

</head>

<body>

@include('web.layouts.header')

<main>

@yield('content')

</main>

@include('web.layouts.footer')

{{-- TOAST NOTIFICATION --}}
@if(session('success') || session('error'))
<div id="web-toast" class="web-toast {{ session('error') ? 'web-toast--error' : 'web-toast--success' }}">
    <div class="web-toast__icon">
        @if(session('error'))
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        @endif
    </div>
    <span class="web-toast__msg">{{ session('success') ?? session('error') }}</span>
    <button class="web-toast__close" onclick="document.getElementById('web-toast').remove()" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
</div>
<script>
    setTimeout(function(){
        var t = document.getElementById('web-toast');
        if (t) { t.classList.add('web-toast--hide'); setTimeout(function(){ if(t) t.remove(); }, 400); }
    }, 4500);
</script>
@endif

@include('web.layouts.login-modal')

@stack('scripts')

</body>

</html>
