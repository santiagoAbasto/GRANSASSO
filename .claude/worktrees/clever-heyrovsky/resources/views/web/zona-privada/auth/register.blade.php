<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('ui.zona_privada.register_title') }} – Gran Sasso</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/web.css'])
</head>
<body style="margin:0;padding:0;min-height:100vh;background:#F4F6FA;display:flex;align-items:center;justify-content:center;font-family:'Open Sans',sans-serif;padding:40px 16px;box-sizing:border-box">

{{-- Card principal — mismos estilos que el login --}}
<div style="width:367px;border-radius:10px;background:#FFF;padding:26px 24px 23px 24px;box-sizing:border-box;box-shadow:0 8px 40px rgba(0,0,0,.12);max-width:calc(100vw - 32px)">

    {{-- Título --}}
    <h1 style="color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:24px;font-style:normal;font-weight:700;line-height:normal;margin:0">
        {{ __('ui.zona_privada.register_title') }}
    </h1>

    @if(session('registered'))
        {{-- Éxito --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:14px;text-align:center;padding:28px 0 8px">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#1F589C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <p style="font-family:'Open Sans',sans-serif;font-size:15px;line-height:1.6;margin:0;color:#1D1E20">
                {{ __('ui.zona_privada.pending_msg') }}
            </p>
        </div>
        <script>setTimeout(function(){ window.location.href = '/'; }, 5000);</script>

    @else

        {{-- Errores --}}
        @if($errors->any())
        <div style="margin-top:16px;background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:10px 14px">
            <ul style="margin:0;padding:0 0 0 16px;font-family:'Open Sans',sans-serif;font-size:13px;color:#C53030">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Formulario --}}
        <form method="POST" action="{{ route('zona-privada.register.store') }}" style="display:flex;flex-direction:column">
            @csrf

            {{-- Nombre --}}
            <label for="name"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.name') }} *
            </label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="given-name"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- Apellido --}}
            <label for="lastname"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.lastname') }} *
            </label>
            <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}" required autocomplete="family-name"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- Email --}}
            <label for="email"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.email') }} *
            </label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- Contraseña --}}
            <label for="password"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.password') }} *
            </label>
            <input type="password" id="password" name="password" required autocomplete="new-password"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- Confirmar contraseña --}}
            <label for="password_confirmation"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.confirm_pass') }} *
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- Botón Registrarse --}}
            <button type="submit"
                    style="margin-top:28px;display:flex;width:315px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;border-radius:22px;background:#1F589C;border:none;color:#FFF;text-align:center;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal;cursor:pointer;box-sizing:border-box;max-width:100%">
                {{ __('ui.zona_privada.register_btn') }}
            </button>
        </form>

        {{-- Separador --}}
        <div style="width:310px;height:1px;background:#EEE;margin:12px auto 0;max-width:100%"></div>

        {{-- ¿Ya tenés cuenta? --}}
        <p style="margin:12px 0 0;color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:12px;font-style:normal;font-weight:400;line-height:normal">
            ¿Ya tenés cuenta?
        </p>

        {{-- Iniciá sesión --}}
        <a href="{{ route('home') }}#zona-privada-modal"
           style="display:block;margin-top:6px;color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:15px;font-style:normal;font-weight:700;line-height:normal;text-decoration:underline;text-decoration-style:solid;text-decoration-skip-ink:none">
            Iniciá sesión
        </a>

    @endif

</div>

</body>
</html>
