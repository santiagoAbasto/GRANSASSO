@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.novedades'))

@section('content')

<div class="zp-page">
    <div class="zp-page__header">
        <h1 class="zp-page__title">{{ __('ui.zona_privada.novedades') }}</h1>
    </div>

    @if($novedades->isEmpty())
        <div class="zp-empty">
            <p>No hay novedades disponibles.</p>
        </div>
    @else
    <div class="zp-novedades-grid">
        @foreach($novedades as $novedad)
        <article class="zp-novedad-card">
            @if($novedad->image)
            <div class="zp-novedad-card__image">
                <img src="{{ Storage::url($novedad->image) }}" alt="{{ $novedad->title }}">
            </div>
            @else
            <div class="zp-novedad-card__image zp-novedad-card__image--placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            @endif

            <div class="zp-novedad-card__body">
                @if($novedad->category)
                <span class="zp-novedad-card__category">{{ strtoupper($novedad->category) }}</span>
                @endif
                <h3 class="zp-novedad-card__title">{{ $novedad->title }}</h3>
                @if($novedad->excerpt)
                <p class="zp-novedad-card__excerpt">{{ $novedad->excerpt }}</p>
                @endif
                <a href="{{ route('zona-privada.novedades.show', $novedad) }}" class="zp-novedad-card__link">
                    {{ __('ui.zona_privada.read_more') }} →
                </a>
            </div>
        </article>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($novedades->hasPages())
    <div class="zp-pagination">
        {{ $novedades->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
