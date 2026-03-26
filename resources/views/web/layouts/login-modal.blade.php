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

        {{-- Errores de sesión --}}
        @if(session('zona_privada_error'))
        <div style="margin-top:14px;background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:10px 14px;font-family:'Open Sans',sans-serif;font-size:13px;color:#C53030">
            {{ session('zona_privada_error') }}
        </div>
        @endif

        @if($errors->has('email') || $errors->has('password'))
        <div style="margin-top:14px;background:#FFF5F5;border:1px solid #FEB2B2;border-radius:8px;padding:10px 14px;font-family:'Open Sans',sans-serif;font-size:13px;color:#C53030">
            {{ $errors->first('email') ?: $errors->first('password') }}
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

        {{-- Separador --}}
        <div style="width:310px;height:1px;background:#EEE;margin:12px auto 0;max-width:100%"></div>

        {{-- ¿No tenés usuario? --}}
        <p style="margin:12px 0 0;color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:12px;font-style:normal;font-weight:400;line-height:normal">
            {{ __('ui.zona_privada.no_account') }}
        </p>

        {{-- Registrate --}}
        <a href="{{ route('zona-privada.register') }}"
           style="display:block;margin-top:6px;color:#1D1E20;text-align:center;font-family:'Open Sans',sans-serif;font-size:15px;font-style:normal;font-weight:700;line-height:normal;text-decoration:underline;text-decoration-style:solid;text-decoration-skip-ink:none">
            {{ __('ui.zona_privada.register_link') }}
        </a>

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
            forgotMsg.style.display = 'flex';
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
