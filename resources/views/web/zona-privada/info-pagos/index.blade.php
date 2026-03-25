@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.payment_info'))

@section('content')

<div class="zp-page">
    <div class="zp-page__header">
        <h1 class="zp-page__title">{{ __('ui.zona_privada.payment_info') }}</h1>
    </div>

    <div class="zp-pagos-grid">

        {{-- LEFT: Bank info --}}
        <div class="zp-card zp-pagos-bank">
            <h2 class="zp-card__subtitle">Datos bancarios</h2>
            @if($bankInfo && !empty($bankInfo->content))
                @foreach($bankInfo->content as $key => $value)
                <div class="zp-bank-row">
                    <span class="zp-bank-row__label">{{ $key }}</span>
                    <span class="zp-bank-row__value">{{ $value }}</span>
                </div>
                @endforeach
            @else
                <p class="zp-empty-text">No hay información bancaria disponible.</p>
            @endif
        </div>

        {{-- RIGHT: Payment form --}}
        <div class="zp-card zp-pagos-form-card">
            <h2 class="zp-card__subtitle">Informar pago</h2>

            @if($errors->any())
            <div class="zp-register-card__errors">
                <ul>
                    @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ route('zona-privada.info-pagos.store') }}"
                  enctype="multipart/form-data"
                  class="zp-register-form">
                @csrf

                <div class="zp-form-row zp-form-row--two">
                    <div class="zp-form-group">
                        <label for="payment_date">Fecha *</label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date') }}" required>
                    </div>
                    <div class="zp-form-group">
                        <label for="amount">Importe *</label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required placeholder="0.00">
                    </div>
                </div>

                <div class="zp-form-row zp-form-row--two">
                    <div class="zp-form-group">
                        <label for="bank">Banco *</label>
                        <input type="text" id="bank" name="bank" value="{{ old('bank') }}" required>
                    </div>
                    <div class="zp-form-group">
                        <label for="branch">Sucursal</label>
                        <input type="text" id="branch" name="branch" value="{{ old('branch') }}">
                    </div>
                </div>

                <div class="zp-form-group">
                    <label for="invoices_cancelled">Facturas canceladas</label>
                    <input type="text" id="invoices_cancelled" name="invoices_cancelled" value="{{ old('invoices_cancelled') }}">
                </div>

                <div class="zp-form-group">
                    <label for="observations">Observaciones / Aclaraciones</label>
                    <textarea id="observations" name="observations" rows="4">{{ old('observations') }}</textarea>
                </div>

                <div class="zp-form-group">
                    <label for="file">Adjuntar archivo *</label>
                    <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                </div>

                <p class="zp-form-required">* Campos obligatorios</p>

                <button type="submit" class="zp-btn zp-btn--primary zp-btn--full">
                    Enviar
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
