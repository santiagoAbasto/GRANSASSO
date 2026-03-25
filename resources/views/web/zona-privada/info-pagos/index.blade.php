@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.payment_info'))

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
            {{ __('ui.zona_privada.payment_info') }}
        </span>
    </nav>

    {{-- Errors --}}
    @if($errors->any())
    <div style="background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:12px 16px;margin-bottom:24px">
        <ul style="margin:0;padding:0 0 0 16px;font-size:13px;color:#C53030;font-family:'Open Sans',sans-serif">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Two-column layout --}}
    <div style="display:grid;grid-template-columns:392px 1fr;gap:24px;align-items:start">

        {{-- LEFT: Datos bancarios --}}
        <div>
            <h2 style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:700;color:#1D1E20;margin:0 0 16px;line-height:150%">
                Cuentas bancarias para efectuar el depósito:
            </h2>

            @if($bankInfo && !empty($bankInfo->content))
            <div style="width:392px">
                @foreach($bankInfo->content as $key => $value)
                <p style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin:0 0 4px">
                    {{ $key }} {{ $value }}
                </p>
                @endforeach
            </div>

            {{-- Separador a 82px del texto --}}
            <div style="margin-top:82px;width:388.5px;height:0;border-top:1px solid #E9E9E9"></div>
            @else
            <p style="font-family:'Open Sans',sans-serif;font-size:14px;color:#6B7280">No hay información bancaria disponible.</p>
            @endif
        </div>

        {{-- RIGHT: Formulario --}}
        <div style="border-radius:6px;background:rgba(38,39,43,0.03);padding:32px">

            <form method="POST"
                  action="{{ route('zona-privada.info-pagos.store') }}"
                  enctype="multipart/form-data">
                @csrf

                {{-- Fecha + Importe --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                    <div>
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Fecha *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date') }}" required
                               style="width:100%;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                    <div>
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Importe *</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required placeholder="0.00"
                               style="width:100%;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                </div>

                {{-- Banco + Sucursal --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
                    <div>
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Banco *</label>
                        <input type="text" name="bank" value="{{ old('bank') }}" required
                               style="width:100%;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                    <div>
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Sucursal</label>
                        <input type="text" name="branch" value="{{ old('branch') }}"
                               style="width:100%;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                </div>

                {{-- Facturas canceladas --}}
                <div style="margin-bottom:20px">
                    <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Facturas canceladas</label>
                    <input type="text" name="invoices_cancelled" value="{{ old('invoices_cancelled') }}"
                           style="width:100%;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff">
                </div>

                {{-- Observaciones --}}
                <div style="margin-bottom:20px">
                    <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Observaciones / Aclaraciones</label>
                    <textarea name="observations" rows="5"
                              style="width:100%;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff;resize:vertical">{{ old('observations') }}</textarea>
                </div>

                {{-- Adjuntar archivo --}}
                <div style="margin-bottom:20px">
                    <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">Adjuntar archivo *</label>
                    <label style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid #E3E0E0;border-radius:6px;background:#fff;cursor:pointer;width:100%;box-sizing:border-box">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style="width:20px;height:20px;aspect-ratio:1/1;flex-shrink:0;opacity:0.5">
                            <path d="M17.5 12.5V15.8333C17.5 16.2754 17.3244 16.6993 17.0118 17.0118C16.6993 17.3244 16.2754 17.5 15.8333 17.5H4.16667C3.72464 17.5 3.30072 17.3244 2.98816 17.0118C2.67559 16.6993 2.5 16.2754 2.5 15.8333V12.5M14.1667 6.66667L10 2.5M10 2.5L5.83333 6.66667M10 2.5V12.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span id="zpFileLabel" style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;opacity:0.5">Seleccionar archivo</span>
                        <input type="file" name="file" id="zpFile" accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                               onchange="document.getElementById('zpFileLabel').textContent=this.files[0]?this.files[0].name:'Seleccionar archivo';document.getElementById('zpFileLabel').style.opacity=this.files[0]?'1':'0.5'">
                    </label>
                </div>

                {{-- Footer del form --}}
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
                    <span style="font-family:'Open Sans',sans-serif;font-size:14px;font-weight:400;color:#1D1E20;line-height:150%">* Campos obligatorios</span>
                    <button type="submit"
                            style="display:flex;width:167px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;border-radius:22px;background:#1F589C;border:none;color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;cursor:pointer;white-space:nowrap;box-sizing:border-box">
                        Enviar
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

@endsection
