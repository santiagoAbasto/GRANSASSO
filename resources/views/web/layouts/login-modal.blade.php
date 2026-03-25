{{-- ══════════════════════════════════════════════
    Zona Privada — Login Modal
    Triggered by btn.btn-zona-privada → opens #zpLoginModal
    ══════════════════════════════════════════════ --}}

<div id="zpLoginModal" class="zp-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="zpModalTitle">
    <div class="zp-modal__overlay" id="zpModalOverlay"></div>

    <div class="zp-modal__dialog">
        <button class="zp-modal__close" id="zpModalClose" aria-label="Cerrar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>

        <div class="zp-modal__logo">
            <svg width="130" height="34" viewBox="0 0 130 34" fill="none">
                <text x="0" y="24" font-family="Open Sans,sans-serif" font-size="20" font-weight="700" fill="#136CBA">Gran Sasso</text>
            </svg>
        </div>

        <h2 class="zp-modal__title" id="zpModalTitle">{{ __('ui.zona_privada.login_title') }}</h2>

        {{-- Session errors --}}
        @if(session('zona_privada_error'))
        <div class="zp-modal__error">{{ session('zona_privada_error') }}</div>
        @endif

        {{-- Validation errors --}}
        @if($errors->has('email') || $errors->has('password'))
        <div class="zp-modal__error">
            {{ $errors->first('email') ?: $errors->first('password') }}
        </div>
        @endif

        {{-- Forgot password panel --}}
        <div id="zpForgotMsg" class="zp-modal__forgot-msg" style="display:none">
            {{ __('ui.zona_privada.forgot_msg') }}
            <button id="zpBackToLogin" class="zp-modal__back-link">← Volver al inicio de sesión</button>
        </div>

        {{-- Login form --}}
        <form id="zpLoginForm" method="POST" action="{{ route('zona-privada.login') }}" class="zp-modal__form">
            @csrf
            <div class="zp-form-group">
                <label for="zpEmail">{{ __('ui.zona_privada.email') }}</label>
                <input type="email" id="zpEmail" name="email" value="{{ old('email') }}"
                       required autocomplete="email" autofocus>
            </div>
            <div class="zp-form-group">
                <label for="zpPassword">{{ __('ui.zona_privada.password') }}</label>
                <input type="password" id="zpPassword" name="password" required autocomplete="current-password">
            </div>

            <button type="button" id="zpForgotBtn" class="zp-modal__link-btn">
                {{ __('ui.zona_privada.forgot') }}
            </button>

            <button type="submit" class="zp-btn zp-btn--primary zp-btn--full">
                {{ __('ui.zona_privada.login_btn') }}
            </button>
        </form>

        <p class="zp-modal__register">
            {{ __('ui.zona_privada.no_account') }}
            <a href="{{ route('zona-privada.register') }}">{{ __('ui.zona_privada.register_link') }}</a>
        </p>
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
    var forgotMsg = document.getElementById('zpForgotMsg');
    var loginForm = document.getElementById('zpLoginForm');
    if (forgotBtn) {
        forgotBtn.addEventListener('click', function() {
            loginForm.style.display = 'none';
            forgotMsg.style.display = 'block';
        });
    }
    var backBtn = document.getElementById('zpBackToLogin');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            forgotMsg.style.display = 'none';
            loginForm.style.display = '';
        });
    }

    // Auto-open if URL hash matches or session flag set
    if (window.location.hash === '#zona-privada-modal'
        || {{ session('zona_privada_modal') ? 'true' : 'false' }}
        || {{ ($errors->has('email') || $errors->has('password')) ? 'true' : 'false' }}
    ) {
        openModal();
    }
}());
</script>
