@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.price_lists'))

@section('content')

<div style="max-width:1224px;margin:0 auto;padding:0 71px;box-sizing:content-box">

    {{-- Breadcrumb — 11px desde el header --}}
    <nav style="display:flex;align-items:center;gap:6px;padding-top:11px;padding-bottom:24px">
        <a href="{{ route('home') }}" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:600;line-height:normal;text-decoration:none">
            {{ __('ui.breadcrumb.home') }}
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" style="width:12px;height:12px;aspect-ratio:1/1;flex-shrink:0">
            <path d="M4.5 9L7.5 6L4.5 3" stroke="#1D1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:300;line-height:normal">
            {{ __('ui.zona_privada.price_lists') }}
        </span>
    </nav>

    {{-- Discount badge --}}
    @if($user->discount_pct > 0 || $user->discount_extra_pct > 0)
    <p style="font-family:'Open Sans',sans-serif;font-size:20px;font-weight:400;color:#1D1E20;margin:0 0 16px;line-height:normal">
        {{ __('ui.zona_privada.discount') }}
        <strong style="color:#1D1E20;font-weight:700">
            {{ $user->discount_pct }}%
            @if($user->discount_extra_pct > 0) + {{ $user->discount_extra_pct }}%@endif
        </strong>
    </p>
    @endif

    {{-- Card --}}
    @if($priceLists->isEmpty())
    <div style="background:#fff;border-radius:8px;padding:48px;text-align:center;color:#6B7280;font-family:'Open Sans',sans-serif;font-size:14px">
        No hay listas de precios disponibles en este momento.
    </div>
    @else
    <div style="background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.06)">

        {{-- Cabecera azul --}}
        <div style="display:grid;grid-template-columns:80px 1fr 120px 120px 300px;align-items:center;height:52px;border-radius:8px 8px 0 0;background:#1F589C;padding:0 24px;gap:16px">
            <span></span>
            <span style="color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:600;line-height:normal">Nombre</span>
            <span style="color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:600;line-height:normal">Formato</span>
            <span style="color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:600;line-height:normal">Peso</span>
            <span></span>
        </div>

        {{-- Filas --}}
        @foreach($priceLists as $i => $pl)
        @if($i > 0)
        <div style="height:0;border-top:1px solid #E3E0E0;margin:0 24px"></div>
        @endif
        <div style="display:grid;grid-template-columns:80px 1fr 120px 120px 300px;align-items:center;padding:16px 24px;gap:16px">

            {{-- Icono archivo --}}
            <div style="width:80px;height:80px;border-radius:6px;border:1px solid #E3E0E0;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg xmlns="http://www.w3.org/2000/svg" width="43" height="43" viewBox="0 0 43 43" fill="none">
                    <path d="M25.0832 3.58333V10.75C25.0832 11.7004 25.4607 12.6118 26.1327 13.2838C26.8047 13.9558 27.7161 14.3333 28.6665 14.3333H35.8332M17.9165 16.125H14.3332M28.6665 23.2917H14.3332M28.6665 30.4583H14.3332M26.8748 3.58333H10.7498C9.79948 3.58333 8.88804 3.96086 8.21604 4.63287C7.54403 5.30487 7.1665 6.21631 7.1665 7.16667V35.8333C7.1665 36.7837 7.54403 37.6951 8.21604 38.3671C8.88804 39.0391 9.79948 39.4167 10.7498 39.4167H32.2498C33.2002 39.4167 34.1116 39.0391 34.7836 38.3671C35.4556 37.6951 35.8332 36.7837 35.8332 35.8333V12.5417L26.8748 3.58333Z" stroke="#00AEEF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

            {{-- Nombre --}}
            <span style="font-family:'Open Sans',sans-serif;font-size:15px;font-weight:600;color:#1D1E20">{{ $pl->name }}</span>

            {{-- Formato --}}
            <span style="font-family:'Open Sans',sans-serif;font-size:14px;color:#6B7280">PDF</span>

            {{-- Peso --}}
            <span style="font-family:'Open Sans',sans-serif;font-size:14px;color:#6B7280">{{ $pl->file_size_human }}</span>

            {{-- Acciones --}}
            <div style="display:flex;align-items:center;gap:12px;justify-content:flex-end">
                <a href="{{ Storage::url($pl->file_path) }}"
                   download
                   style="display:flex;width:138px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;box-sizing:border-box;border:1px solid #1F589C;border-radius:22px;background:#fff;color:#1F589C;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;text-decoration:none;white-space:nowrap;transition:background .2s">
                    {{ __('ui.zona_privada.download') }}
                </a>
                <a href="{{ Storage::url($pl->file_path) }}"
                   target="_blank"
                   style="display:flex;width:138px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;box-sizing:border-box;border-radius:22px;background:#1F589C;color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;text-decoration:none;white-space:nowrap;transition:background .2s">
                    {{ __('ui.zona_privada.view_online') }}
                </a>
            </div>

        </div>
        @endforeach

    </div>
    @endif

</div>

@endsection
