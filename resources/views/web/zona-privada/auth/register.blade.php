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
<body class="zp-register-page">

<div class="zp-register-wrap">
    <div class="zp-register-card">

        <a href="{{ route('home') }}" class="zp-register-card__logo">
            <svg width="140" height="36" viewBox="0 0 140 36" fill="none">
                <text x="0" y="26" font-family="Open Sans,sans-serif" font-size="22" font-weight="700" fill="#136CBA">Gran Sasso</text>
            </svg>
        </a>

        <h1 class="zp-register-card__title">{{ __('ui.zona_privada.register_title') }}</h1>

        @if(session('registered'))
            <div class="zp-register-card__success">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#136CBA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <p>{{ __('ui.zona_privada.pending_msg') }}</p>
            </div>
            <script>
                setTimeout(function(){ window.location.href = '/'; }, 5000);
            </script>
        @else

        @if($errors->any())
            <div class="zp-register-card__errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('zona-privada.register.store') }}" class="zp-register-form">
            @csrf

            <div class="zp-form-row zp-form-row--two">
                <div class="zp-form-group">
                    <label for="name">{{ __('ui.zona_privada.name') }} *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="given-name">
                </div>
                <div class="zp-form-group">
                    <label for="lastname">{{ __('ui.zona_privada.lastname') }} *</label>
                    <input type="text" id="lastname" name="lastname" value="{{ old('lastname') }}" required autocomplete="family-name">
                </div>
            </div>

            <div class="zp-form-group">
                <label for="email">{{ __('ui.zona_privada.email') }} *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>

            <div class="zp-form-group">
                <label for="password">{{ __('ui.zona_privada.password') }} *</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>

            <div class="zp-form-group">
                <label for="password_confirmation">{{ __('ui.zona_privada.confirm_pass') }} *</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>

            <button type="submit" class="zp-btn zp-btn--primary zp-btn--full">
                {{ __('ui.zona_privada.register_btn') }}
            </button>
        </form>

        <p class="zp-register-card__login-link">
            ¿Ya tenés cuenta? <a href="{{ route('home') }}#zona-privada-modal">Iniciá sesión</a>
        </p>

        @endif

    </div>
</div>

</body>
</html>
