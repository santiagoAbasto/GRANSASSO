@extends('web.layouts.app')

@section('title', 'Contacto')

@section('content')

@php
$footer = json_decode(
    \App\Models\Setting::where('key','footer')->value('value'),
    true
);
$address  = $footer['address']  ?? '';
$email    = $footer['email']    ?? '';
$phone    = $footer['phone']    ?? '';
$phoneDig = preg_replace('/\D/', '', $phone);
$mapsUrl  = 'https://maps.app.goo.gl/5yuFSFCiKuQLhQ3i8';
@endphp

<section class="contact-page">
<div class="container">

    {{-- BREADCRUMB --}}
    <div class="breadcrumb">
        <a href="{{ route('home') }}">Inicio</a>
        <span>›</span>
        <span>Contacto</span>
    </div>

    {{-- 3-COLUMN GRID --}}
    <div class="contact-grid">

        {{-- COL 1: INFO --}}
        <div class="contact-info">

            <p class="contact-info__text">
                Para mayor información, no dude en contactarse mediante el siguiente formulario,
                o a través de nuestras vías de comunicación.
            </p>

            <div class="contact-info__items">

                @if($address)
                <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="contact-info__row">
                    <span class="contact-info__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M13.832 16.568C14.0385 16.6628 14.2712 16.6845 14.4917 16.6294C14.7122 16.5744 14.9073 16.4458 15.045 16.265L15.4 15.8C15.5863 15.5516 15.8279 15.35 16.1056 15.2111C16.3833 15.0723 16.6895 15 17 15H20C20.5304 15 21.0391 15.2107 21.4142 15.5858C21.7893 15.9609 22 16.4696 22 17V20C22 20.5304 21.7893 21.0391 21.4142 21.4142C21.0391 21.7893 20.5304 22 20 22C15.2261 22 10.6477 20.1036 7.27208 16.7279C3.89642 13.3523 2 8.7739 2 4C2 3.46957 2.21071 2.96086 2.58579 2.58579C2.96086 2.21071 3.46957 2 4 2H7C7.53043 2 8.03914 2.21071 8.41421 2.58579C8.78929 2.96086 9 3.46957 9 4V7C9 7.31049 8.92771 7.61672 8.78885 7.89443C8.65 8.17214 8.44839 8.41371 8.2 8.6L7.732 8.951C7.54842 9.09118 7.41902 9.29059 7.36579 9.51535C7.31256 9.74012 7.33878 9.97638 7.44 10.184C8.80668 12.9599 11.0544 15.2048 13.832 16.568Z" stroke="#136CBA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="contact-info__label">{!! nl2br(e($address)) !!}</span>
                </a>
                @endif

                @if($email)
                <a href="mailto:{{ $email }}" class="contact-info__row">
                    <span class="contact-info__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M22 7L13.009 12.727C12.7039 12.9042 12.3573 12.9976 12.0045 12.9976C11.6517 12.9976 11.3051 12.9042 11 12.727L2 7M4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6C2 4.89543 2.89543 4 4 4Z" stroke="#136CBA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="contact-info__label">{{ $email }}</span>
                </a>
                @endif

                @if($phone)
                <a href="https://wa.me/{{ $phoneDig }}" target="_blank" rel="noopener noreferrer" class="contact-info__row">
                    <span class="contact-info__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5823 11.985C14.3328 11.8608 13.1095 11.2625 12.8817 11.1792C12.6539 11.0967 12.4881 11.0558 12.3215 11.3042C12.1557 11.5508 11.6793 12.1092 11.5344 12.2742C11.3887 12.44 11.2438 12.46 10.9952 12.3367C10.7465 12.2117 9.94429 11.9508 8.99391 11.1075C8.25453 10.4509 7.75464 9.64002 7.60978 9.39169C7.46492 9.14419 7.59387 9.01002 7.71863 8.88669C7.83084 8.77585 7.96732 8.59752 8.09209 8.45335C8.21685 8.30835 8.25788 8.20502 8.34078 8.03919C8.42451 7.87419 8.38265 7.73002 8.31985 7.60585C8.25788 7.48169 7.7605 6.26252 7.55284 5.76669C7.35104 5.28419 7.14589 5.35003 6.99349 5.34169C6.8478 5.33503 6.682 5.33336 6.51621 5.33336C6.35041 5.33336 6.08079 5.39503 5.85303 5.64336C5.62444 5.89086 4.98219 6.49002 4.98219 7.70919C4.98219 8.92752 5.87313 10.105 5.99789 10.2709C6.12266 10.4359 7.75213 12.9375 10.2482 14.01C10.8428 14.265 11.3058 14.4175 11.6667 14.5308C12.2629 14.72 12.8055 14.6933 13.2342 14.6292C13.7115 14.5583 14.7063 14.03 14.9139 13.4517C15.1208 12.8733 15.1207 12.3775 15.0588 12.2742C14.9968 12.1708 14.8319 12.1092 14.5823 11.985ZM10.0423 18.1542H10.0389C8.55634 18.1544 7.10099 17.7578 5.8254 17.0058L5.52396 16.8275L2.39062 17.6458L3.22712 14.6058L3.03035 14.2942C2.20149 12.9811 1.76286 11.4615 1.76512 9.91085C1.7668 5.36919 5.47958 1.6742 10.0456 1.6742C12.2562 1.6742 14.3345 2.53253 15.897 4.08919C16.6676 4.85301 17.2785 5.76133 17.6941 6.7616C18.1098 7.76188 18.322 8.83425 18.3186 9.91668C18.3169 14.4583 14.6041 18.1542 10.0423 18.1542ZM17.086 2.9067C16.1634 1.98247 15.0657 1.24965 13.8564 0.7507C12.6472 0.251754 11.3505 -0.00339687 10.0414 3.41479e-05C4.55347 3.41479e-05 0.0854091 4.44586 0.0837344 9.91002C0.0811914 11.649 0.539563 13.3578 1.4126 14.8642L0 20L5.27861 18.6217C6.73884 19.4134 8.37519 19.8283 10.0381 19.8283H10.0423C15.5302 19.8283 19.9983 15.3825 20 9.91752C20.004 8.61525 19.7485 7.32511 19.2484 6.12172C18.7482 4.91833 18.0132 3.82559 17.086 2.9067Z" fill="#136CBA"/>
                        </svg>
                    </span>
                    <span class="contact-info__label">{{ $phone }}</span>
                </a>
                @endif

            </div>

        </div>

        {{-- COL 2 + 3: FORM (spans 2 cols internally) --}}
        <form class="contact-form" action="{{ route('contact.store') }}" method="POST">
            @csrf

            @if(session('success'))
            <div class="contact-form__success" style="grid-column: 1 / -1;">
                {{ session('success') }}
            </div>
            @endif

            {{-- FORM INNER GRID: 2 cols --}}
            <div class="contact-form__inner">

                {{-- LEFT COLUMN --}}
                <div class="contact-form__col">

                    <div class="contact-field">
                        <label class="contact-field__label" for="name">Nombre y apellido*</label>
                        <input type="text" id="name" name="name" class="contact-field__input"
                               value="{{ old('name') }}" required>
                        @error('name')<span class="contact-field__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="contact-field">
                        <label class="contact-field__label" for="phone">Teléfono*</label>
                        <input type="tel" id="phone" name="phone" class="contact-field__input"
                               value="{{ old('phone') }}" required>
                        @error('phone')<span class="contact-field__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="contact-field contact-field--textarea">
                        <label class="contact-field__label" for="message">Mensaje*</label>
                        <textarea id="message" name="message" class="contact-field__textarea"
                                  required>{{ old('message') }}</textarea>
                        @error('message')<span class="contact-field__error">{{ $message }}</span>@enderror
                    </div>

                </div>

                {{-- RIGHT COLUMN --}}
                <div class="contact-form__col contact-form__col--right">

                    <div class="contact-field">
                        <label class="contact-field__label" for="email">Email*</label>
                        <input type="email" id="email" name="email" class="contact-field__input"
                               value="{{ old('email') }}" required>
                        @error('email')<span class="contact-field__error">{{ $message }}</span>@enderror
                    </div>

                    <div class="contact-field">
                        <label class="contact-field__label" for="company">Empresa</label>
                        <input type="text" id="company" name="company" class="contact-field__input"
                               value="{{ old('company') }}">
                    </div>

                    <div class="contact-form__submit-area">
                        <span class="contact-form__required">*Campos Obligatorios</span>
                        <button type="submit" class="contact-form__btn">Enviar</button>
                    </div>

                </div>

            </div>

        </form>

    </div>

</div>
</section>

@endsection
