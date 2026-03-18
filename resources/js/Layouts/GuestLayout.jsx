export default function GuestLayout({ children }) {
    return (
        <div className="gs-auth">

            {/* ── Panel izquierdo: marca ── */}
            <div className="gs-auth__brand">
                <div className="gs-auth__brand-inner">
                    <img
                        src="/storage/images/logos/logo-white.png"
                        alt="Gran Sasso"
                        className="gs-auth__logo"
                    />
                    <span className="gs-auth__brand-sub">Instrumentación Industrial</span>
                    <span className="gs-auth__brand-sub">Bienvenido al panel de administracion</span>

                </div>
            </div>

            {/* ── Panel derecho: formulario ── */}
            <div className="gs-auth__form-panel">
                <div className="gs-auth__form-box">
                    {children}
                </div>
            </div>

        </div>
    );
}
