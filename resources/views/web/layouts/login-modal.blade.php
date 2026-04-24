{{-- ══════════════════════════════════════════════
    Zona Privada — Login Modal
    Triggered by btn.btn-zona-privada → opens #zpLoginModal
    ══════════════════════════════════════════════ --}}

<div id="zpLoginModal" class="zp-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="zpModalTitle">
    <div class="zp-modal__overlay" id="zpModalOverlay"></div>

    {{-- Card principal --}}
    <div class="zp-modal__dialog"
         style="width:367px;min-height:439px;height:auto;border-radius:10px;background:#FFF;padding:26px 24px 23px 24px;box-sizing:border-box;box-shadow:0 8px 40px rgba(0,0,0,.18);position:relative;max-width:calc(100vw - 32px)">

        {{-- Botón cerrar --}}
        <button class="zp-modal__close" id="zpModalClose" aria-label="Cerrar"
                style="position:absolute;top:14px;right:14px;background:none;border:none;cursor:pointer;color:#9CA3AF;padding:4px;display:flex;align-items:center;line-height:0">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>

        {{-- Título --}}
        <h2 id="zpModalTitle"
            style="color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:24px;font-style:normal;font-weight:700;line-height:normal;margin:0">
            {{ __('ui.zona_privada.login_title') }}
        </h2>

        {{-- Errores login --}}
        @if(session('zona_privada_error'))
        <div style="margin-top:14px;background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:10px 14px;font-family:'Open Sans',sans-serif;font-size:13px;color:#C53030">
            {{ session('zona_privada_error') }}
        </div>
        @endif

        @if($errors->has('email') || $errors->has('password'))
        <div id="zpLoginError" style="margin-top:14px;background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:10px 14px;font-family:'Open Sans',sans-serif;font-size:13px;color:#C53030">
            {{ $errors->first('email') ?: $errors->first('password') }}
        </div>
        @endif

        {{-- Éxito registro --}}
        @if(session('registered'))
        <div id="zpRegisterSuccess" style="margin-top:14px;background:#F0FFF4;border:1px solid #9AE6B4;border-radius:8px;padding:12px 14px;font-family:'Open Sans',sans-serif;font-size:13px;color:#276749">
            {{ __('ui.zona_privada.pending_msg') }}
        </div>
        @endif

        {{-- Errores registro --}}
        @if($errors->zonaPrivadaRegister->any())
        <div id="zpRegisterError" style="margin-top:14px;background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:10px 14px">
            <ul style="margin:0;padding:0 0 0 16px;font-family:'Open Sans',sans-serif;font-size:13px;color:#C53030">
                @foreach($errors->zonaPrivadaRegister->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Panel olvidé contraseña --}}
        <div id="zpForgotMsg" style="display:none;margin-top:14px;background:#EBF8FF;border:1px solid #BEE3F8;border-radius:8px;padding:14px 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;flex-direction:column;gap:10px">
            {{ __('ui.zona_privada.forgot_msg') }}
            <button id="zpBackToLogin"
                    style="font-size:13px;color:#1F589C;background:none;border:none;cursor:pointer;text-decoration:underline;padding:0;font-family:'Open Sans',sans-serif;text-align:left">
                ← Volver al inicio de sesión
            </button>
        </div>

        {{-- Formulario login --}}
        <form id="zpLoginForm" method="POST" action="{{ route('zona-privada.login') }}" style="display:flex;flex-direction:column">
            @csrf

            {{-- Usuario --}}
            <label for="zpEmail"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.email') }}
            </label>
            <input type="email" id="zpEmail" name="email" value="{{ old('email') }}"
                   required autocomplete="email" autofocus
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- Contraseña --}}
            <label for="zpPassword"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.password') }}
            </label>
            <input type="password" id="zpPassword" name="password"
                   required autocomplete="current-password"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            {{-- ¿Olvidaste tu contraseña? --}}
            <button type="button" id="zpForgotBtn"
                    style="margin-top:6px;align-self:flex-end;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:12px;font-style:normal;font-weight:400;line-height:normal;text-decoration:underline;background:none;border:none;cursor:pointer;padding:0">
                {{ __('ui.zona_privada.forgot') }}
            </button>

            {{-- Botón Ingresar --}}
            <button type="submit"
                    style="margin-top:28px;display:flex;width:315px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;border-radius:22px;background:#1F589C;border:none;color:#FFF;text-align:center;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal;cursor:pointer;box-sizing:border-box;max-width:100%">
                {{ __('ui.zona_privada.login_btn') }}
            </button>
        </form>

        {{-- Formulario registro --}}
        <form id="zpRegisterForm" method="POST" action="{{ route('zona-privada.register.store') }}" style="display:none;flex-direction:column">
            @csrf

            <label for="zpRegisterName"
                   style="margin-top:24px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.name') }} *
            </label>
            <input type="text" id="zpRegisterName" name="name" value="{{ old('name') }}"
                   required autocomplete="given-name"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            <label for="zpRegisterLastname"
                   style="margin-top:16px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.lastname') }} *
            </label>
            <input type="text" id="zpRegisterLastname" name="lastname" value="{{ old('lastname') }}"
                   required autocomplete="family-name"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            <label for="zpRegisterEmail"
                   style="margin-top:16px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.email') }} *
            </label>
            <input type="email" id="zpRegisterEmail" name="email" value="{{ old('email') }}"
                   required autocomplete="email"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            <label for="zpRegisterPassword"
                   style="margin-top:16px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.password') }} *
            </label>
            <input type="password" id="zpRegisterPassword" name="password"
                   required autocomplete="new-password"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            <label for="zpRegisterPasswordConfirmation"
                   style="margin-top:16px;color:#1D1E20;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal">
                {{ __('ui.zona_privada.confirm_pass') }} *
            </label>
            <input type="password" id="zpRegisterPasswordConfirmation" name="password_confirmation"
                   required autocomplete="new-password"
                   style="margin-top:9px;width:319px;height:45px;border-radius:24px;border:1px solid #E3E0E0;background:rgba(255,255,255,0.93);box-sizing:border-box;padding:0 16px;font-family:'Open Sans',sans-serif;font-size:14px;color:#1D1E20;outline:none;max-width:100%">

            <button type="submit"
                    style="margin-top:24px;display:flex;width:315px;padding:12px 25px;justify-content:center;align-items:center;gap:10px;border-radius:22px;background:#1F589C;border:none;color:#FFF;text-align:center;font-family:'Open Sans',sans-serif;font-size:16px;font-style:normal;font-weight:400;line-height:normal;cursor:pointer;box-sizing:border-box;max-width:100%">
                {{ __('ui.zona_privada.register_btn') }}
            </button>

            <button type="button" id="zpBackToLoginFromRegister"
                    style="margin-top:10px;align-self:center;color:#1F589C;font-family:'Open Sans',sans-serif;font-size:13px;font-weight:600;line-height:normal;text-decoration:underline;background:none;border:none;cursor:pointer;padding:0">
                Volver al inicio de sesión
            </button>
        </form>

        {{-- Separador --}}
        <div id="zpBottomDivider" style="width:310px;height:1px;background:#EEE;margin:12px auto 0;max-width:100%"></div>

        {{-- ¿No tenés usuario? --}}
        <p id="zpBottomText" style="margin:12px 0 0;color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:12px;font-style:normal;font-weight:400;line-height:normal">
            {{ __('ui.zona_privada.no_account') }}
        </p>

        {{-- Registrate --}}
        <button type="button" id="zpShowRegister"
           style="display:block;width:100%;margin-top:6px;color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:15px;font-style:normal;font-weight:700;line-height:normal;text-decoration:underline;text-decoration-style:solid;text-decoration-skip-ink:none;background:none;border:none;cursor:pointer;padding:0">
            {{ __('ui.zona_privada.register_link') }}
        </button>

    </div>
</div>

<script>
(function () {
    // Open modal
    function openModal() {
        var m = document.getElementById('zpLoginModal');
        m.classList.add('zp-modal--open');
        m.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        var f = document.getElementById('zpEmail');
        if (f) setTimeout(function(){ f.focus(); }, 100);
    }

    // Close modal
    function closeModal() {
        var m = document.getElementById('zpLoginModal');
        m.classList.remove('zp-modal--open');
        m.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    var title = document.getElementById('zpModalTitle');
    var loginForm = document.getElementById('zpLoginForm');
    var registerForm = document.getElementById('zpRegisterForm');
    var forgotMsg = document.getElementById('zpForgotMsg');
    var bottomDivider = document.getElementById('zpBottomDivider');
    var bottomText = document.getElementById('zpBottomText');
    var showRegisterBtn = document.getElementById('zpShowRegister');
    var registerSuccess = document.getElementById('zpRegisterSuccess');
    var loginError = document.getElementById('zpLoginError');
    var registerError = document.getElementById('zpRegisterError');
    var registerCompleted = {{ session('registered') ? 'true' : 'false' }};

    function setAuxVisibility(showBottomArea) {
        if (bottomDivider) bottomDivider.style.display = showBottomArea ? 'block' : 'none';
        if (bottomText) bottomText.style.display = showBottomArea ? 'block' : 'none';
        if (showRegisterBtn) showRegisterBtn.style.display = showBottomArea ? 'block' : 'none';
    }

    function showLoginPanel() {
        if (title) title.textContent = @json(__('ui.zona_privada.login_title'));
        if (loginForm) loginForm.style.display = 'flex';
        if (registerForm) registerForm.style.display = 'none';
        if (forgotMsg) forgotMsg.style.display = 'none';
        if (registerSuccess) registerSuccess.style.display = 'none';
        if (registerError) registerError.style.display = 'none';
        if (loginError) loginError.style.display = 'block';
        setAuxVisibility(true);
    }

    function showRegisterPanel() {
        if (title) title.textContent = @json(__('ui.zona_privada.register_title'));
        if (loginForm) loginForm.style.display = 'none';
        if (registerForm) registerForm.style.display = registerCompleted ? 'none' : 'flex';
        if (forgotMsg) forgotMsg.style.display = 'none';
        if (loginError) loginError.style.display = 'none';
        if (registerError) registerError.style.display = registerCompleted ? 'none' : 'block';
        if (registerSuccess) registerSuccess.style.display = registerCompleted ? 'block' : 'none';
        setAuxVisibility(false);
        if (!registerCompleted) {
            var firstField = document.getElementById('zpRegisterName');
            if (firstField) setTimeout(function(){ firstField.focus(); }, 50);
        }
    }

    function showForgotPanel() {
        if (title) title.textContent = @json(__('ui.zona_privada.login_title'));
        if (loginForm) loginForm.style.display = 'none';
        if (registerForm) registerForm.style.display = 'none';
        if (forgotMsg) forgotMsg.style.display = 'flex';
        if (registerSuccess) registerSuccess.style.display = 'none';
        if (registerError) registerError.style.display = 'none';
        if (loginError) loginError.style.display = 'none';
        setAuxVisibility(false);
    }

    // Wire up all "Zona Privada" buttons
    document.querySelectorAll('.btn-zona-privada').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            openModal();
        });
    });

    // Close on X
    var closeBtn = document.getElementById('zpModalClose');
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    // Close on overlay click
    var overlay = document.getElementById('zpModalOverlay');
    if (overlay) overlay.addEventListener('click', closeModal);

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // Forgot password
    var forgotBtn = document.getElementById('zpForgotBtn');
    if (forgotBtn) {
        forgotBtn.addEventListener('click', function() {
            showForgotPanel();
        });
    }
    var backBtn = document.getElementById('zpBackToLogin');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            showLoginPanel();
        });
    }

    var registerBtn = document.getElementById('zpShowRegister');
    if (registerBtn) {
        registerBtn.addEventListener('click', function() {
            showRegisterPanel();
        });
    }

    var backToLoginFromRegister = document.getElementById('zpBackToLoginFromRegister');
    if (backToLoginFromRegister) {
        backToLoginFromRegister.addEventListener('click', function() {
            showLoginPanel();
        });
    }

    // Auto-open if URL hash matches or session flag set
    if (window.location.hash === '#zona-privada-modal'
        || {{ session('zona_privada_modal') ? 'true' : 'false' }}
        || {{ ($errors->has('email') || $errors->has('password')) ? 'true' : 'false' }}
        || {{ $errors->zonaPrivadaRegister->any() ? 'true' : 'false' }}
        || {{ session('registered') ? 'true' : 'false' }}
    ) {
        openModal();
    }

    if ({{ ($errors->zonaPrivadaRegister->any() || session('zona_privada_register') || session('registered')) ? 'true' : 'false' }}) {
        showRegisterPanel();
    } else {
        showLoginPanel();
    }
}());
</script>
