@extends('web.zona-privada.layouts.app')

@section('title', $novedad->getLocalizedTitle())

@section('content')

<div class="zp-section-wrap">

    <nav style="display:flex;align-items:center;gap:2px;padding-top:11px;padding-bottom:32px;flex-wrap:wrap">
        <a href="{{ route('home') }}" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:600;line-height:normal;text-decoration:none">
            {{ __('ui.breadcrumb.home') }}
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" style="width:12px;height:12px;flex-shrink:0">
            <path d="M4.5 9L7.5 6L4.5 3" stroke="#1D1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <a href="{{ route('zona-privada.novedades') }}" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:600;line-height:normal;text-decoration:none">
            {{ __('ui.zona_privada.novedades') }}
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" style="width:12px;height:12px;flex-shrink:0">
            <path d="M4.5 9L7.5 6L4.5 3" stroke="#1D1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:300;line-height:normal">
            {{ Str::limit($novedad->getLocalizedTitle(), 40) }}
        </span>
    </nav>

    <div style="max-width:800px">

        @php $cat = $novedad->getLocalizedCategory(); @endphp
        @if($cat)
        <span style="font-family:'Open Sans',sans-serif;font-size:11px;font-weight:700;color:#1F589C;letter-spacing:0.08em;line-height:normal;text-transform:uppercase">
            {{ $cat }}
        </span>
        @endif

        <h1 class="zp-post-title" style="font-family:'Open Sans',sans-serif;font-size:28px;font-weight:700;color:#1D1E20;line-height:130%;margin:12px 0 20px">
            {{ $novedad->getLocalizedTitle() }}
        </h1>

        @if($novedad->image)
        <div style="width:100%;border-radius:8px;overflow:hidden;margin-bottom:24px">
            <img src="{{ Storage::url($novedad->image) }}" alt="{{ $novedad->getLocalizedTitle() }}" style="width:100%;height:auto;display:block">
        </div>
        @endif

        @php $excerpt = $novedad->getLocalizedExcerpt(); @endphp
        @if($excerpt)
        <p style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:600;color:#1D1E20;line-height:150%;margin:0 0 20px">
            {!! nl2br(e($excerpt)) !!}
        </p>
        @endif

        @php $body = $novedad->getLocalizedBody(); @endphp
        @if($body)
        <div style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:175%">
            {!! $body !!}
        </div>
        @endif

        <div style="margin-top:40px;padding-top:24px;border-top:1px solid #E3E0E0">
            <a href="{{ route('zona-privada.novedades') }}"
               style="font-family:'Open Sans',sans-serif;font-size:14px;font-weight:600;color:#1F589C;text-decoration:none">
                ← {{ __('ui.zona_privada.news_back') }}
            </a>
        </div>

    </div>

</div>
<div style="height:105px"></div>

@endsection
