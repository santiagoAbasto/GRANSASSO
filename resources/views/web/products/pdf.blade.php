<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale ?? app()->getLocale()) }}">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #1a1a2e;
    background: #fff;
    padding: 30px 40px 40px 40px;
}

@page {
    margin: 20mm 20mm 26mm 20mm;
}

/* ── PAGE NUMBER ────────────────────────────────── */
.pagenum:before { content: counter(page); }

/* ── HEADER ─────────────────────────────────────── */
.hd {
    display: table;
    width: 100%;
    padding-bottom: 10px;
    margin-bottom: 16px;
    border-bottom: 2px solid #1F589C;
}
.hd__l { display: table-cell; vertical-align: middle; width: 55%; }
.hd__r { display: table-cell; vertical-align: middle; width: 45%; text-align: right; }

/* ── HERO ────────────────────────────────────────── */
.hero {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.hero__l {
    display: table-cell;
    vertical-align: middle;
    width: 65%;
}
.hero__r {
    display: table-cell;
    vertical-align: middle;
    width: 35%;
    text-align: right;
}
.hero__brand-label {
    font-size: 7.5px;
    font-weight: bold;
    color: #9CA3AF;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 4px;
}
.hero__oem {
    font-size: 22px;
    font-weight: bold;
    color: #1F589C;
    letter-spacing: 0.5px;
    line-height: 1;
}
.hero__name {
    font-size: 9px;
    color: #6B7280;
    margin-top: 5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.hero__divider {
    border: none;
    border-top: 1px solid #E5E7EB;
    margin-bottom: 16px;
}

/* ── TWO-COLUMN BODY ─────────────────────────────── */
.body { display: table; width: 100%; }
.body__left  { display: table-cell; width: 36%; vertical-align: top; padding-right: 20px; }
.body__right { display: table-cell; width: 64%; vertical-align: top; }

/* ── MAIN IMAGE ──────────────────────────────────── */
.img-main {
    text-align: center;
    margin-bottom: 15px;
}
.img-main img {
    max-width: 100%;
    max-height: 200px;
    width: auto;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* ── EXTRA IMAGES STACK ────────────────────────── */
.img-extra {
    text-align: center;
    margin-bottom: 15px;
}
.img-extra img {
    max-width: 100%;
    max-height: 200px;
    width: auto;
    height: auto;
    display: block;
    margin: 0 auto;
}

/* ── SECTION TITLE ───────────────────────────────── */
.sec {
    font-size: 7px;
    font-weight: bold;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    padding-bottom: 4px;
    border-bottom: 1px solid #E5E7EB;
    margin-bottom: 7px;
    margin-top: 16px;
}
.sec:first-child { margin-top: 0; }

/* ── DATA TABLE ──────────────────────────────────── */
.dt { width: 100%; border-collapse: collapse; font-size: 8.5px; }
.dt tr { border-bottom: 1px solid #F3F4F6; }
.dt tr:last-child { border-bottom: none; }
.dt td { padding: 5px 4px; vertical-align: top; line-height: 1.45; }
.dt td:first-child { width: 40%; color: #9CA3AF; font-weight: bold; padding-right: 8px; }
.dt td:last-child  { color: #111827; }

/* ── DESCRIPTION ─────────────────────────────────── */
.desc {
    font-size: 8.5px;
    color: #374151;
    line-height: 1.7;
}

/* ── APPLICATIONS ────────────────────────────────── */
.apps { width: 100%; border-collapse: collapse; font-size: 8.5px; }
.apps tr { border-bottom: 1px solid #F3F4F6; }
.apps tr:last-child { border-bottom: none; }
.apps td { padding: 4px 4px; color: #374151; line-height: 1.4; }

/* ── GEARBOX SECTION ─────────────────────────────── */
.gb {
    margin-top: 22px;
    page-break-inside: avoid;
    border-top: 1px solid #E5E7EB;
    padding-top: 16px;
}
.gb__title {
    font-size: 7px;
    font-weight: bold;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    margin-bottom: 12px;
}
.gb__grid { display: table; width: 100%; }
.gb__cell { display: table-cell; vertical-align: top; padding-right: 24px; }
.gb__cell:last-child { padding-right: 0; }
.gb__label {
    font-size: 7px;
    font-weight: bold;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 3px;
}
.gb__value {
    font-size: 10px;
    font-weight: bold;
    color: #1F589C;
    line-height: 1.3;
}

.badge {
    display: inline-block;
    background: #EFF6FF;
    color: #1E40AF;
    border: 1px solid #BFDBFE;
    border-radius: 2px;
    font-size: 7.5px;
    font-weight: bold;
    padding: 2px 5px;
    margin: 1px 2px 1px 0;
}

.gb__compat {
    margin-top: 12px;
    border-top: 1px solid #F3F4F6;
    padding-top: 10px;
}
.gb__compat-label {
    font-size: 7px;
    font-weight: bold;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 3px;
}
.gb__compat-val {
    font-size: 8.5px;
    color: #374151;
    line-height: 1.5;
}

/* ── FOOTER FLOW ─────────────────────────────────── */
.footer-flow {
    margin-top: 80px;
    border-top: 1px solid #E5E7EB;
    padding-top: 10px;
    font-size: 8px;
    color: #9CA3AF;
    text-align: center;
}
</style>
</head>
<body>

@php
    $gsLogo    = storage_path('app/public/images/logos/logo-color.png');
    $gsLogoSvg = storage_path('app/public/images/logos/logo-color.svg');
    $generatedAt = now('America/Argentina/Buenos_Aires')->format('d/m/Y H:i');

    if (file_exists($gsLogoSvg)) {
        $logoPath = $gsLogoSvg;
    } elseif (file_exists($gsLogo)) {
        $logoPath = $gsLogo;
    } else {
        $logoPath = null;
    }

    $extraImgs = $allImages->filter(fn($img) => !$mainImage || $img->id !== $mainImage->id)->values();
@endphp

{{-- ── HEADER ──────────────────────────────────────────────────────── --}}
<div class="hd">
    <div class="hd__l">
        @if($logoPath)
            <img src="{{ $logoPath }}" alt="GranSasso" style="max-height:48px; max-width:180px;">
        @else
            <div style="font-size:22px; font-weight:900; color:#1F589C; letter-spacing:-0.5px;">GRANSASSO</div>
            <div style="font-size:6.5px; font-weight:bold; color:#9CA3AF; letter-spacing:3px; text-transform:uppercase; margin-top:2px;">REPUESTOS DE TRANSMISIÓN</div>
        @endif
    </div>
    <div class="hd__r">
        <div style="font-size:16px; font-weight:bold; color:#1F589C; letter-spacing:1px; text-transform:uppercase;">{{ __('ui.product_pdf.title') }}</div>
        <div style="font-size:7px; font-weight:bold; color:#9CA3AF; text-transform:uppercase; letter-spacing:2px; margin-top:3px;">{{ __('ui.product_pdf.product_information') }}</div>
    </div>
</div>

{{-- ── HERO ─────────────────────────────────────────────────────────── --}}
<div class="hero">
    <div class="hero__l">
        <div class="hero__brand-label">{{ $brand->getName() }}</div>
        <div class="hero__oem">{{ $oemCode }}</div>
        @if($productName)
        <div class="hero__name">{{ mb_strtoupper($productName) }}</div>
        @endif
    </div>
    <div class="hero__r">
        @if($brand->logo)
            <img src="{{ storage_path('app/public/' . $brand->logo) }}" alt="{{ $brand->getName() }}" style="max-height:30px; max-width:100px;">
        @else
            <span style="font-size:11px; font-weight:bold; color:#6B7280; text-transform:uppercase; letter-spacing:1px;">{{ $brand->getName() }}</span>
        @endif
    </div>
</div>
<hr class="hero__divider">

{{-- ── BODY ─────────────────────────────────────────────────────────── --}}
<div class="body">

    {{-- Columna izquierda: imágenes --}}
    <div class="body__left">

        <div class="img-main">
            @if($mainImage)
                <img src="{{ storage_path('app/public/' . $mainImage->path) }}" alt="{{ $oemCode }}">
            @else
                <div style="height:130px; display:table; width:100%;">
                    <div style="display:table-cell; vertical-align:middle; text-align:center; color:#D1D5DB; font-size:8px; text-transform:uppercase; letter-spacing:1px;">{{ __('ui.product_pdf.no_image') }}</div>
                </div>
            @endif
        </div>

        @if($extraImgs->isNotEmpty())
            @foreach($extraImgs as $img)
            <div class="img-extra">
                <img src="{{ storage_path('app/public/' . $img->path) }}" alt="">
            </div>
            @endforeach
        @endif

    </div>

    {{-- Columna derecha: datos --}}
    <div class="body__right">

        {{-- Especificaciones --}}
        @if($codeGs || $figure || $refOriginal || $refsAdicionales->isNotEmpty() || $altOemCodes->isNotEmpty())
        <div class="sec">{{ __('ui.product_pdf.specifications') }}</div>
        <table class="dt">
            @if($codeGs)
            <tr><td>{{ __('ui.product_pdf.gs_code') }}</td><td>{{ $codeGs }}</td></tr>
            @endif
            @if($figure)
            <tr><td>{{ __('ui.product_pdf.figure') }}</td><td>{{ $figure }}</td></tr>
            @endif
            @if($refOriginal)
            <tr><td>{{ __('ui.product_pdf.original_ref') }}</td><td>{{ $refOriginal }}</td></tr>
            @endif
            @if($refsAdicionales->isNotEmpty())
            <tr><td>{{ __('ui.product_pdf.additional_refs') }}</td><td>{{ $refsAdicionales->implode(' ; ') }}</td></tr>
            @endif
            @if($altOemCodes->isNotEmpty())
            <tr><td>{{ __('ui.product_pdf.alt_oem_codes') }}</td><td>{{ $altOemCodes->implode(' / ') }}</td></tr>
            @endif
        </table>
        @endif

        {{-- Aplicaciones --}}
        @if($applications->isNotEmpty())
        <div class="sec">{{ __('ui.product_pdf.compatible_applications') }}</div>
        <table class="apps">
            @foreach($applications as $app)
            <tr><td>{{ $app }}</td></tr>
            @endforeach
        </table>
        @endif

        {{-- Descripción --}}
        @if($productDesc)
        <div class="sec">{{ __('ui.product_pdf.description') }}</div>
        <div class="desc">{{ ucfirst(mb_strtolower($productDesc)) }}</div>
        @endif

    </div>
</div>

{{-- ── GEARBOX ──────────────────────────────────────────────────────── --}}
<div class="gb">
    <div class="gb__title">{{ __('ui.product_pdf.transmission_gearbox') }}</div>
    <div class="gb__grid">
        <div class="gb__cell">
            <div class="gb__label">{{ __('ui.product_pdf.brand') }}</div>
            <div class="gb__value">{{ $brand->getName() }}</div>
        </div>
        <div class="gb__cell">
            <div class="gb__label">{{ __('ui.product_pdf.model') }}</div>
            <div class="gb__value">{{ lang($gearbox->name) }}</div>
        </div>
        @if($variants->isNotEmpty())
        <div class="gb__cell">
            <div class="gb__label">{{ __('ui.product_pdf.variants') }}</div>
            <div style="margin-top:3px;">
                @foreach($variants as $v)<span class="badge">{{ $v }}</span>@endforeach
            </div>
        </div>
        @endif
    </div>

    @if(lang($gearbox->compatible_vehicles))
    <div class="gb__compat">
        <div class="gb__compat-label">{{ __('ui.product_pdf.compatible_vehicles') }}</div>
        <div class="gb__compat-val">{{ lang($gearbox->compatible_vehicles) }}</div>
    </div>
    @endif
</div>

{{-- ── FOOTER IN FLOW ────────────────────────────────────────────── --}}
<div class="footer-flow">
    <strong style="color:#6B7280;">GranSasso Repuestos</strong> &nbsp;·&nbsp; {{ __('ui.product_pdf.generated_on', ['date' => $generatedAt]) }}
</div>

</body>
</html>
