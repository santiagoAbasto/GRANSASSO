@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.novedades'))

@section('content')

<div style="max-width:1224px;margin:0 auto;padding:0 71px;box-sizing:content-box">

    {{-- Breadcrumb --}}
    <nav style="display:flex;align-items:center;gap:2px;padding-top:11px;padding-bottom:32px">
        <a href="{{ route('home') }}" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:600;line-height:normal;text-decoration:none">
            {{ __('ui.breadcrumb.home') }}
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" style="width:12px;height:12px;aspect-ratio:1/1;flex-shrink:0">
            <path d="M4.5 9L7.5 6L4.5 3" stroke="#1D1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:300;line-height:normal">
            {{ __('ui.zona_privada.novedades') }}
        </span>
    </nav>

    @if($novedades->isEmpty())
    <div style="padding:48px;text-align:center;color:#6B7280;font-family:'Open Sans',sans-serif;font-size:14px">
        No hay novedades disponibles.
    </div>
    @else

    {{-- Grid de novedades --}}
    <div style="display:flex;flex-wrap:wrap;gap:24px">

        @foreach($novedades as $novedad)
        <a href="{{ route('zona-privada.novedades.show', $novedad) }}"
           style="text-decoration:none;color:inherit;display:flex;width:392px;flex-direction:column;align-items:flex-start;border-radius:8px;border:1px solid #DDDDE0;overflow:hidden;flex-shrink:0">

            {{-- Imagen --}}
            @if($novedad->image)
            <div style="height:300px;align-self:stretch;border-radius:8px 8px 0 0;background:url('{{ Storage::url($novedad->image) }}') lightgray 50% / cover no-repeat;flex-shrink:0"></div>
            @else
            <div style="height:300px;align-self:stretch;border-radius:8px 8px 0 0;background:#F4F6FA;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C0C0C0" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </div>
            @endif

            {{-- Contenido --}}
            <div style="padding:24px 24px 25px 24px;display:flex;flex-direction:column;align-items:flex-start;flex:1;width:100%;box-sizing:border-box">

                @if($novedad->category)
                <span style="align-self:stretch;color:#1F589C;font-family:'Open Sans',sans-serif;font-size:14px;font-weight:700;line-height:150%">
                    {{ $novedad->category }}
                </span>
                @endif

                <h3 style="align-self:stretch;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:24px;font-weight:600;line-height:120%;margin:7px 0 0">
                    {{ $novedad->getLocalizedTitle() }}
                </h3>

                @php $excerpt = $novedad->getLocalizedExcerpt(); @endphp
                @if($excerpt)
                <p style="width:343px;height:40px;flex-shrink:0;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:150%;margin:43px 0 0;overflow:hidden">
                    {{ Str::limit(strip_tags($excerpt), 80) }}
                </p>
                @endif

                <div style="display:flex;align-items:center;gap:0;margin-top:auto;padding-top:16px;opacity:0.5">
                    <span style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:150%">
                        {{ __('ui.zona_privada.read_more') }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 4L10.59 5.41L16.17 11H4V13H16.17L10.59 18.59L12 20L20 12L12 4Z" fill="#414047"/>
                    </svg>
                </div>

            </div>
        </a>
        @endforeach

    </div>

    {{-- Paginación --}}
    @if($novedades->hasPages())
    <div style="display:flex;justify-content:center;padding:40px 0 0">
        {{ $novedades->links() }}
    </div>
    @endif

    @endif

</div>

@endsection
