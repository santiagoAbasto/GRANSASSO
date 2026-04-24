@extends('web.zona-privada.layouts.app')

@section('title', __('ui.zona_privada.payment_info'))

@section('content')

<div class="zp-section-wrap">

    <nav class="zp-breadcrumb" style="display:flex;align-items:center;gap:2px;padding-top:11px;padding-bottom:52px">
        <a href="{{ route('home') }}" style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:600;line-height:normal;text-decoration:none">
            {{ __('ui.breadcrumb.home') }}
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none" style="width:12px;height:12px;flex-shrink:0">
            <path d="M4.5 9L7.5 6L4.5 3" stroke="#1D1E20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span style="color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-weight:300;line-height:normal">
            {{ __('ui.zona_privada.payment_info') }}
        </span>
    </nav>

    @if($errors->any())
    <div style="background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:12px 16px;margin-bottom:24px">
        <ul style="margin:0;padding:0 0 0 16px;font-size:13px;color:#C53030;font-family:'Open Sans',sans-serif">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="zp-pagos-grid" style="display:grid;grid-template-columns:388px 1fr;gap:28px;align-items:start;padding-bottom:86px">

        {{-- LEFT --}}
        <div>
            <h2 style="font-family:'Open Sans',sans-serif;font-size:18px;font-weight:600;color:#1D1E20;margin:0 0 24px;line-height:120%">
                {{ __('ui.zona_privada.bank_title') }}
            </h2>

            @if($bankInfo && !empty($bankInfo->content))
            <div>
                @php
                    $items = $bankInfo->content;
                    $isArrayOfPairs = is_array($items) && isset($items[0]) && is_array($items[0]);
                @endphp
                @if($isArrayOfPairs)
                    @foreach($items as [$key, $value])
                    <p style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin:0 0 4px">
                        {{ $key }}: {{ $value }}
                    </p>
                    @endforeach
                @else
                    @foreach($items as $key => $value)
                    <p style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin:0 0 4px">
                        {{ $key }}: {{ $value }}
                    </p>
                    @endforeach
                @endif
            </div>
            <div style="margin-top:82px;height:0;border-top:1px solid #E9E9E9"></div>
            @else
            <p style="font-family:'Open Sans',sans-serif;font-size:14px;color:#6B7280">{{ __('ui.zona_privada.no_bank_info') }}</p>
            @endif
        </div>

        {{-- RIGHT --}}
        <div class="zp-pagos-card">

            <form method="POST" action="{{ route('zona-privada.info-pagos.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="zp-form-row-2" style="display:flex;gap:16px;margin-bottom:20px;width:100%">
                    <div style="flex:1;min-width:0">
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">{{ __('ui.zona_privada.field_date') }} *</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date') }}" required
                            style="width:100%;height:45px;padding:0 22px;border:1px solid #E3E0E0;border-radius:23px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                    <div style="flex:1;min-width:0">
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">{{ __('ui.zona_privada.field_amount') }} *</label>
                        <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0" required placeholder="0.00"
                            style="width:100%;height:45px;padding:0 22px;border:1px solid #E3E0E0;border-radius:23px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                </div>

                <div class="zp-form-row-3" style="display:flex;gap:20px;margin-bottom:20px;width:100%">
                    <div style="flex:1;min-width:0">
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:8px">{{ __('ui.zona_privada.field_bank') }} *</label>
                        <input type="text" name="bank" value="{{ old('bank') }}" required
                            style="width:100%;height:45px;padding:0 22px;border:1px solid #E3E0E0;border-radius:23px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                    <div style="flex:1;min-width:0">
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:8px">{{ __('ui.zona_privada.field_branch') }} *</label>
                        <input type="text" name="branch" value="{{ old('branch') }}" required
                            style="width:100%;height:45px;padding:0 22px;border:1px solid #E3E0E0;border-radius:23px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                    <div style="flex:1;min-width:0">
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:8px">{{ __('ui.zona_privada.field_invoices') }}</label>
                        <input type="text" name="invoices_cancelled" value="{{ old('invoices_cancelled') }}"
                            style="width:100%;height:45px;padding:0 22px;border:1px solid #E3E0E0;border-radius:23px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;box-sizing:border-box;outline:none;background:#fff">
                    </div>
                </div>

                <div style="margin-bottom:20px;width:100%">
                    <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">{{ __('ui.zona_privada.field_observations') }}</label>
                    <textarea name="observations"
                        style="width:100%;height:170px;padding:16px 22px;border:1px solid #E3E0E0;border-radius:24px;font-family:'Open Sans',sans-serif;font-size:16px;color:#1D1E20;line-height:150%;box-sizing:border-box;outline:none;background:#fff;resize:none">{{ old('observations') }}</textarea>
                </div>

                <div class="zp-form-footer" style="display:flex;align-items:flex-end;justify-content:space-between;width:100%">
                    <div style="width:374px;flex-shrink:0" class="zp-file-wrap">
                        <label style="display:block;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;margin-bottom:6px">{{ __('ui.zona_privada.field_file') }} *</label>
                        <label style="display:flex;align-items:center;justify-content:space-between;padding:0 22px;border:1px solid #E9E9E9;border-radius:23px;background:#fff;cursor:pointer;height:45px;box-sizing:border-box">
                            <span id="zpFileLabel" style="font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;color:#1D1E20;line-height:150%;opacity:0.5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ __('ui.zona_privada.file_placeholder') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" style="width:20px;height:20px;flex-shrink:0;opacity:0.5">
                                <path d="M17.5 12.5V15.8333C17.5 16.2754 17.3244 16.6993 17.0118 17.0118C16.6993 17.3244 16.2754 17.5 15.8333 17.5H4.16667C3.72464 17.5 3.30072 17.3244 2.98816 17.0118C2.67559 16.6993 2.5 16.2754 2.5 15.8333V12.5M14.1667 6.66667L10 2.5M10 2.5L5.83333 6.66667M10 2.5V12.5" stroke="#1D1E20" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input type="file" name="file" id="zpFile" accept=".pdf,.jpg,.jpeg,.png" style="display:none"
                                onchange="document.getElementById('zpFileLabel').textContent=this.files[0]?this.files[0].name:'{{ __('ui.zona_privada.file_placeholder') }}';document.getElementById('zpFileLabel').style.opacity=this.files[0]?'1':'0.5'">
                        </label>
                    </div>
                    <div class="zp-submit-wrap" style="display:flex;align-items:center;gap:20px;height:45px;flex-shrink:0">
                        <span style="font-family:'Open Sans',sans-serif;font-size:14px;font-weight:400;color:#1D1E20;line-height:150%">{{ __('ui.zona_privada.required_fields') }}</span>
                        <button type="submit" style="display:flex;width:167px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;border-radius:23px;background:#1F589C;border:none;color:#FFF;font-family:'Open Sans',sans-serif;font-size:16px;font-weight:400;line-height:normal;cursor:pointer;white-space:nowrap;box-sizing:border-box">
                            {{ __('ui.zona_privada.btn_submit') }}
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </div>
</div>

@endsection
