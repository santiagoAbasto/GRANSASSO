<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9.5px;
    color: #1D1E20;
    background: #fff;
    padding: 0 32px 0 32px;
}

/* ════════════════════════════════════════
   HEADER
════════════════════════════════════════ */
.hd {
    display: table;
    width: 100%;
    padding: 20px 0 14px;
    border-bottom: 3px solid #1F589C;
}
.hd__l { display: table-cell; vertical-align: middle; width: 60%; }
.hd__r { display: table-cell; vertical-align: middle; width: 40%; text-align: right; }

.hd__label {
    font-size: 7px;
    font-weight: 700;
    color: #1F589C;
    letter-spacing: 2.5px;
    text-transform: uppercase;
}
.hd__brand-logo img  { max-height: 28px; max-width: 120px; }
.hd__brand-text {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    letter-spacing: 0.5px;
}

/* ════════════════════════════════════════
   HERO STRIP (código + nombre, fondo azul)
════════════════════════════════════════ */
.hero {
    background: #1F589C;
    color: #fff;
    padding: 14px 18px;
    margin-top: 16px;
    border-radius: 6px;
}
.hero__brand {
    font-size: 8px;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    opacity: 0.75;
    margin-bottom: 3px;
}
.hero__oem {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: 0.5px;
}
.hero__name {
    font-size: 10px;
    opacity: 0.85;
    margin-top: 4px;
    line-height: 1.4;
}

/* ════════════════════════════════════════
   BODY: dos columnas
════════════════════════════════════════ */
.body {
    display: table;
    width: 100%;
    margin-top: 16px;
}
.body__left  { display: table-cell; width: 34%; vertical-align: top; padding-right: 18px; }
.body__right { display: table-cell; width: 66%; vertical-align: top; }

/* ── Imagen principal ─────────────────── */
.main-img-box {
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    background: #fff;
    padding: 12px;
    text-align: center;
    margin-bottom: 10px;
}
.main-img-box img {
    max-width: 100%;
    max-height: 170px;
    display: block;
    margin: 0 auto;
}

/* ── Galería extra ────────────────────── */
.gallery {
    display: table;
    width: 100%;
    margin-top: 0;
}
.gallery-row { display: table-row; }
.gallery-cell {
    display: table-cell;
    width: 33.33%;
    padding: 2px;
}
.gallery-cell__inner {
    border: 1px solid #E5E7EB;
    border-radius: 5px;
    background: #fff;
    padding: 5px;
    text-align: center;
}
.gallery-cell__inner img {
    max-width: 100%;
    max-height: 55px;
    display: block;
    margin: 0 auto;
}

/* ── Marca bajo imagen ────────────────── */
.brand-box {
    text-align: center;
    margin-top: 10px;
    padding: 8px;
    border: 1px solid #F3F4F6;
    border-radius: 6px;
    background: #F9FAFB;
}
.brand-box img  { max-height: 26px; max-width: 110px; }
.brand-box-text {
    font-size: 9px;
    font-weight: 700;
    color: #374151;
}

/* ── Tabla de datos ───────────────────── */
.section-title {
    font-size: 7px;
    font-weight: 700;
    color: #1F589C;
    text-transform: uppercase;
    letter-spacing: 1.8px;
    border-bottom: 1.5px solid #1F589C;
    padding-bottom: 4px;
    margin-bottom: 8px;
    margin-top: 16px;
}
.section-title:first-child { margin-top: 0; }

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}
.data-table td {
    padding: 5px 4px;
    vertical-align: top;
    border-bottom: 1px solid #F3F4F6;
    line-height: 1.5;
}
.data-table td:first-child {
    width: 36%;
    color: #6B7280;
    font-weight: 700;
    white-space: nowrap;
    padding-right: 10px;
}
.data-table td:last-child { color: #1D1E20; }
.data-table tr:last-child td { border-bottom: none; }

/* ── Aplicaciones ─────────────────────── */
.apps-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}
.apps-table td {
    padding: 4px 6px;
    border-bottom: 1px solid #F9FAFB;
    color: #374151;
    line-height: 1.4;
}
.apps-table tr:last-child td { border-bottom: none; }

/* ════════════════════════════════════════
   GEARBOX SECTION
════════════════════════════════════════ */
.gearbox-section {
    border-top: 2px solid #E5E7EB;
    margin-top: 18px;
    padding-top: 12px;
}
.gearbox-grid { display: table; width: 100%; }
.gearbox-cell { display: table-cell; width: 33.33%; vertical-align: top; padding-right: 12px; }
.gearbox-cell__label {
    font-size: 7px;
    font-weight: 700;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 3px;
}
.gearbox-cell__value {
    font-size: 9.5px;
    color: #1D1E20;
    line-height: 1.5;
    font-weight: 600;
}

.badge {
    display: inline-block;
    background: #EFF6FF;
    color: #1F589C;
    border: 1px solid #BFDBFE;
    border-radius: 3px;
    font-size: 8px;
    font-weight: 700;
    padding: 2px 8px;
    margin: 2px 3px 0 0;
}

/* ════════════════════════════════════════
   FOOTER
════════════════════════════════════════ */
.footer {
    border-top: 1px solid #E5E7EB;
    margin-top: 22px;
    padding: 8px 0 16px;
    font-size: 7.5px;
    color: #9CA3AF;
}
</style>
</head>
<body>

@php
    $gsLogo    = storage_path('app/public/images/logos/logo-color.svg');
    $extraImgs = $allImages->filter(fn($img) => !$mainImage || $img->id !== $mainImage->id)->values();
@endphp

{{-- ════ HEADER ════════════════════════════════════════════ --}}
<div class="hd">
    <div class="hd__l">
        @if(file_exists($gsLogo))
            <img src="{{ $gsLogo }}" alt="GranSasso" style="max-height:44px; max-width:150px;">
        @else
            <span style="font-size:20px; font-weight:700; color:#1F589C;">GranSasso</span>
            <span style="display:block; font-size:7px; color:#9CA3AF; letter-spacing:3px; text-transform:uppercase; margin-top:2px;">Engranajes</span>
        @endif
    </div>
    <div class="hd__r">
        <div class="hd__label">Ficha Técnica de Producto</div>
        @if($brand->logo)
        <div class="hd__brand-logo" style="margin-top:6px;">
            <img src="{{ storage_path('app/public/' . $brand->logo) }}" alt="{{ $brand->getName() }}">
        </div>
        @else
        <div class="hd__brand-text" style="margin-top:6px;">{{ $brand->getName() }}</div>
        @endif
    </div>
</div>

{{-- ════ HERO ═════════════════════════════════════════════ --}}
<div class="hero">
    <div class="hero__brand">{{ $brand->getName() }}</div>
    <div class="hero__oem">{{ $oemCode }}</div>
    @if($productName)
    <div class="hero__name">{{ ucwords(mb_strtolower($productName)) }}</div>
    @endif
</div>

{{-- ════ BODY ══════════════════════════════════════════════ --}}
<div class="body">

    {{-- ── Columna izquierda: imágenes ─────────────────── --}}
    <div class="body__left">

        {{-- Imagen principal --}}
        <div class="main-img-box">
            @if($mainImage)
                <img src="{{ storage_path('app/public/' . $mainImage->path) }}" alt="{{ $oemCode }}">
            @else
                <div style="height:140px; color:#D1D5DB; font-size:9px; padding-top:58px; text-align:center;">Sin imagen</div>
            @endif
        </div>

        {{-- Imágenes extra (hasta 6 en grid 3×2) --}}
        @if($extraImgs->isNotEmpty())
        <div class="gallery">
            @foreach($extraImgs->chunk(3) as $row)
            <div class="gallery-row">
                @foreach($row as $img)
                <div class="gallery-cell">
                    <div class="gallery-cell__inner">
                        <img src="{{ storage_path('app/public/' . $img->path) }}" alt="">
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
        @endif

        {{-- Logo marca --}}
        @if($brand->logo || $brand->getName())
        <div class="brand-box" style="margin-top:10px;">
            @if($brand->logo)
                <img src="{{ storage_path('app/public/' . $brand->logo) }}" alt="{{ $brand->getName() }}">
            @else
                <div class="brand-box-text">{{ $brand->getName() }}</div>
            @endif
        </div>
        @endif

    </div>

    {{-- ── Columna derecha: datos ──────────────────────── --}}
    <div class="body__right">

        {{-- Datos del producto --}}
        @if($codeGs || $figure || $refOriginal || $refsAdicionales->isNotEmpty() || $altOemCodes->isNotEmpty())
        <div class="section-title">Datos del producto</div>
        <table class="data-table">
            @if($codeGs)
            <tr><td>Código GS</td><td>{{ $codeGs }}</td></tr>
            @endif
            @if($figure)
            <tr><td>Figura</td><td>{{ $figure }}</td></tr>
            @endif
            @if($refOriginal)
            <tr><td>Ref. Original</td><td>{{ $refOriginal }}</td></tr>
            @endif
            @if($refsAdicionales->isNotEmpty())
            <tr><td>Refs. Adicionales</td><td>{{ $refsAdicionales->implode(' ; ') }}</td></tr>
            @endif
            @if($altOemCodes->isNotEmpty())
            <tr><td>OEM Alternativos</td><td>{{ $altOemCodes->implode(' / ') }}</td></tr>
            @endif
        </table>
        @endif

        {{-- Aplicaciones --}}
        @if($applications->isNotEmpty())
        <div class="section-title">Aplicaciones</div>
        <table class="apps-table">
            @foreach($applications as $app)
            <tr><td>{{ $app }}</td></tr>
            @endforeach
        </table>
        @endif

        {{-- Descripción --}}
        @if($productDesc)
        <div class="section-title">Descripción</div>
        <p style="font-size:9px; color:#374151; line-height:1.7;">{{ ucwords(mb_strtolower($productDesc)) }}</p>
        @endif

    </div>
</div>

{{-- ════ INFORMACIÓN DE CAJA ══════════════════════════════ --}}
<div class="gearbox-section">
    <div class="section-title" style="margin-top:0; border-bottom-color:#E5E7EB; color:#374151;">Información de caja</div>
    <div class="gearbox-grid">
        <div class="gearbox-cell">
            <div class="gearbox-cell__label">Marca</div>
            <div class="gearbox-cell__value">{{ $brand->getName() }}</div>
        </div>
        <div class="gearbox-cell">
            <div class="gearbox-cell__label">Modelo</div>
            <div class="gearbox-cell__value">{{ $gearbox->name }}</div>
        </div>
        @if($variants->isNotEmpty())
        <div class="gearbox-cell">
            <div class="gearbox-cell__label">Compatibilidad</div>
            <div style="margin-top:3px;">
                @foreach($variants as $v)<span class="badge">{{ $v }}</span>@endforeach
            </div>
        </div>
        @endif
    </div>

    @if($gearbox->compatible_vehicles)
    <div style="margin-top:10px;">
        <div class="gearbox-cell__label">Vehículos compatibles</div>
        <div class="gearbox-cell__value" style="margin-top:3px; font-weight:400;">{{ $gearbox->compatible_vehicles }}</div>
    </div>
    @endif
</div>

{{-- ════ FOOTER ════════════════════════════════════════════ --}}
<div class="footer">
    GranSasso Repuestos &mdash; Ficha generada el {{ now()->format('d/m/Y') }}
</div>

</body>
</html>
