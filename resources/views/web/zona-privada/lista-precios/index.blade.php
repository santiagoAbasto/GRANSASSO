@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.price_lists'))

@section('content')

<div class="zp-page">
    <div class="zp-page__header">
        <h1 class="zp-page__title">{{ __('ui.zona_privada.price_lists') }}</h1>
    </div>

    {{-- Discount badge --}}
    @if($user->discount_pct > 0 || $user->discount_extra_pct > 0)
    <div class="zp-discount">
        {{ __('ui.zona_privada.discount') }}
        <strong>
            {{ $user->discount_pct }}%
            @if($user->discount_extra_pct > 0)
                + {{ $user->discount_extra_pct }}%
            @endif
        </strong>
    </div>
    @endif

    @if($priceLists->isEmpty())
        <div class="zp-empty">
            <p>No hay listas de precios disponibles en este momento.</p>
        </div>
    @else
    <div class="zp-card">
        <table class="zp-table">
            <thead>
                <tr>
                    <th style="width:48px"></th>
                    <th>Nombre</th>
                    <th>Formato</th>
                    <th>Tamaño</th>
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($priceLists as $pl)
                <tr>
                    <td class="zp-table__icon-cell">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#136CBA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </td>
                    <td class="zp-table__name">{{ $pl->name }}</td>
                    <td>PDF</td>
                    <td>{{ $pl->file_size_human }}</td>
                    <td class="zp-table__actions">
                        <a href="{{ Storage::url($pl->file_path) }}"
                           download
                           class="zp-btn zp-btn--outlined zp-btn--sm">
                            {{ __('ui.zona_privada.download') }}
                        </a>
                        <a href="{{ Storage::url($pl->file_path) }}"
                           target="_blank"
                           class="zp-btn zp-btn--navy zp-btn--sm">
                            {{ __('ui.zona_privada.view_online') }}
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
