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
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px">

        @foreach($novedades as $novedad)
        <article style="border-radius:8px;overflow:hidden;border:1px solid #E3E0E0;background:#fff;display:flex;flex-direction:column">

            {{-- Imagen --}}
            @if($novedad->image)
            <div style="width:100%;aspect-ratio:16/9;overflow:hidden;flex-shrink:0">
                <img src="{{ Storage::url($novedad->image) }}"
                     alt="{{ $novedad->title }}"
                     style="width:100%;height:100%;object-fit:cover">
            </div>
            @else
            <div style="width:100%;aspect-ratio:16/9;background:#F4F6FA;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#C0C0C0" stroke-width="1.5">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <polyline points="21 15 16 10 5 21"/>
                </svg>
            </div>
            @endif

            {{-- Contenido --}}
            <div style="padding:20px 24px 24px;display:flex;flex-direction:column;flex:1;gap:8px">

                @if($novedad->category)
                <span style="font-family:'Open Sans',sans-serif;font-size:11px;font-weight:700;color:#1F589C;letter-spacing:0.08em;line-height:normal;text-transform:uppercase">
                    {{ $novedad->category }}
                </span>
                @endif

                <h3 style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:700;color:#1D1E20;line-height:140%;margin:0">
                    {{ $novedad->getLocalizedTitle() }}
                </h3>

                @php $excerpt = $novedad->getLocalizedExcerpt(); @endphp
                @if($excerpt)
                <p style="font-family:'Open Sans',sans-serif;font-size:14px;font-weight:400;color:#6B7280;line-height:150%;margin:0;flex:1">
                    {{ Str::limit(strip_tags($excerpt), 120) }}
                </p>
                @endif

                <a href="{{ route('zona-privada.novedades.show', $novedad) }}"
                   style="font-family:'Open Sans',sans-serif;font-size:14px;font-weight:600;color:#1F589C;text-decoration:none;line-height:normal;margin-top:4px">
                    {{ __('ui.zona_privada.read_more') }} →
                </a>

            </div>
        </article>
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
