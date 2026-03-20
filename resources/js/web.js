/* =============================================
   WEB TOAST — iOS-style notification
   Llamar con: showWebToast(mensaje, 'success' | 'error')
============================================= */

(function () {

    var SVG_OK = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">'
        + '<circle cx="12" cy="12" r="11" fill="#136CBA" opacity="0.12"/>'
        + '<path d="M7 12.5l3.5 3.5 6.5-7" stroke="#136CBA" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>'
        + '</svg>';

    var SVG_ERR = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">'
        + '<circle cx="12" cy="12" r="11" fill="#ef4444" opacity="0.12"/>'
        + '<path d="M15 9L9 15M9 9l6 6" stroke="#ef4444" stroke-width="2.1" stroke-linecap="round"/>'
        + '</svg>';

    var SVG_X = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none">'
        + '<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>'
        + '</svg>';

    function showWebToast(message, type) {
        type = type || 'success';

        /* Elimina toasts anteriores */
        document.querySelectorAll('.web-toast').forEach(function (t) { t.remove(); });

        var toast = document.createElement('div');
        toast.className = 'web-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        toast.innerHTML =
            '<span class="web-toast__icon">' + (type === 'success' ? SVG_OK : SVG_ERR) + '</span>'
            + '<span class="web-toast__msg">' + message + '</span>'
            + '<button class="web-toast__close" aria-label="Cerrar">' + SVG_X + '</button>';

        document.body.appendChild(toast);

        function dismiss() {
            toast.classList.add('web-toast--hide');
            setTimeout(function () { if (toast.parentNode) toast.remove(); }, 340);
        }

        toast.querySelector('.web-toast__close').addEventListener('click', dismiss);

        /* Auto-dismiss: 4s éxito / 6s error */
        var timer = setTimeout(dismiss, type === 'error' ? 6000 : 4000);

        /* Pausa al pasar el mouse */
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () { timer = setTimeout(dismiss, 2000); });
    }

    window.showWebToast = showWebToast;

}());


/* =============================================
   NEWSLETTER — AJAX (sin recarga de página)
   Intercepta el submit, envía con fetch,
   muestra toast iOS inline y se queda en el footer.
============================================= */

document.addEventListener("DOMContentLoaded", () => {

    const form = document.getElementById("newsletterForm");
    const msg  = document.getElementById("newsletterMsg");   // aria-live oculto

    if (form && msg) {

        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const btn   = form.querySelector("button[type='submit']");
            const input = form.querySelector("input[type='email']");
            const csrf  = form.querySelector("input[name='_token']").value;

            /* Estado: enviando */
            btn.disabled      = true;
            btn.style.opacity = "0.6";
            msg.textContent   = "";

            try {
                const res = await fetch(form.action, {
                    method:  "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                        "X-CSRF-TOKEN": csrf,
                        "Accept":       "application/json",
                    },
                    body: new URLSearchParams({ email: input.value, _token: csrf }),
                });

                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    input.value     = "";
                    msg.textContent = "¡Suscripción realizada!";
                    showWebToast("¡Te suscribiste correctamente!", "success");
                } else {
                    const errores  = data?.errors?.email;
                    const errorMsg = errores
                        ? errores[0]
                        : (data?.message || "Ocurrió un error. Intentá de nuevo.");
                    msg.textContent = errorMsg;
                    showWebToast(errorMsg, "error");
                }

            } catch {
                const errMsg = "Error de red. Intentá de nuevo.";
                msg.textContent = errMsg;
                showWebToast(errMsg, "error");
            }

            btn.disabled      = false;
            btn.style.opacity = "";
        });
    }

});


document.addEventListener("DOMContentLoaded", () => {

    /* =============================================
       MOBILE NAVBAR — JS fix
       Garantiza position:fixed en mobile/tablet
       independientemente de quirks de iOS Safari.
       overflow-x: clip en html (reset.css) resuelve
       el horizontal overflow del panel de nav.
    ============================================= */

    const nav = document.getElementById("mainNav");

    if (nav) {
        function applyMobileFixed() {
            if (window.innerWidth <= 1024) {
                nav.style.position = "fixed";
                nav.style.top      = "0";
                nav.style.left     = "0";
                nav.style.right    = "0";
                nav.style.width    = "100%";
                nav.style.zIndex   = "100";
                /* Compensa la altura del navbar para que el contenido no quede tapado */
                document.body.style.paddingTop = nav.offsetHeight + "px";
            } else {
                /* Desktop: quita los estilos inline y deja actuar al CSS */
                nav.style.position = "";
                nav.style.top      = "";
                nav.style.left     = "";
                nav.style.right    = "";
                nav.style.width    = "";
                nav.style.zIndex   = "";
                document.body.style.paddingTop = "";
            }
        }

        applyMobileFixed();
        window.addEventListener("resize", applyMobileFixed, { passive: true });
    }


    /* =============================================
       HERO SLIDER
       - Auto-avance cada 6s
       - Dots sincronizan con el slide activo
       - Click en dot navega al slide correspondiente
       - Funciona con cualquier cantidad de slides
    ============================================= */

    const heroSection = document.querySelector(".hero");

    if (heroSection) {

        const slides   = heroSection.querySelectorAll(".hero-slide");
        const dots     = heroSection.querySelectorAll(".hero-dot");
        const contents = heroSection.querySelectorAll(".hero-content");

        if (slides.length > 1) {

            let currentIndex = 0;
            let autoplay;

            function heroGoTo(index) {
                slides[currentIndex].classList.remove("active");
                if (dots[currentIndex])     dots[currentIndex].classList.remove("active");
                if (contents[currentIndex]) contents[currentIndex].classList.remove("active");

                const oldIframe = slides[currentIndex].querySelector("iframe");
                if (oldIframe) oldIframe.src = "";

                currentIndex = (index + slides.length) % slides.length;

                slides[currentIndex].classList.add("active");
                if (dots[currentIndex])     dots[currentIndex].classList.add("active");
                if (contents[currentIndex]) contents[currentIndex].classList.add("active");

                const newIframe = slides[currentIndex].querySelector("iframe");
                if (newIframe && newIframe.dataset.src) {
                    newIframe.src = newIframe.dataset.src;
                }
            }

            function startAutoplay() {
                autoplay = setInterval(() => heroGoTo(currentIndex + 1), 6000);
            }

            function resetAutoplay() {
                clearInterval(autoplay);
                startAutoplay();
            }

            dots.forEach((dot, i) => {
                dot.addEventListener("click", () => {
                    if (i !== currentIndex) { heroGoTo(i); resetAutoplay(); }
                });
            });

            startAutoplay();
        }
    }

    /* =============================================
       PRODUCTS BANNER SLIDER
       - Mismo comportamiento que el hero slider
       - Soporte imagen y video (data-src en iframe)
    ============================================= */

    const productsBanner = document.querySelector(".products-banner");

    if (productsBanner) {

        const pbSlides = productsBanner.querySelectorAll(".products-banner__slide");
        const pbDots   = productsBanner.querySelectorAll(".products-banner__dot");

        if (pbSlides.length > 1) {

            let pbCurrent = 0;
            let pbAutoplay;

            function pbGoTo(index) {
                pbSlides[pbCurrent].classList.remove("active");
                if (pbDots[pbCurrent]) pbDots[pbCurrent].classList.remove("active");

                const oldIframe = pbSlides[pbCurrent].querySelector("iframe");
                if (oldIframe) oldIframe.src = "";

                pbCurrent = (index + pbSlides.length) % pbSlides.length;

                pbSlides[pbCurrent].classList.add("active");
                if (pbDots[pbCurrent]) pbDots[pbCurrent].classList.add("active");

                const newIframe = pbSlides[pbCurrent].querySelector("iframe");
                if (newIframe && newIframe.dataset.src) {
                    newIframe.src = newIframe.dataset.src;
                }
            }

            function pbStartAutoplay() {
                pbAutoplay = setInterval(() => pbGoTo(pbCurrent + 1), 6000);
            }

            function pbResetAutoplay() {
                clearInterval(pbAutoplay);
                pbStartAutoplay();
            }

            pbDots.forEach((dot, i) => {
                dot.addEventListener("click", () => {
                    if (i !== pbCurrent) { pbGoTo(i); pbResetAutoplay(); }
                });
            });

            pbStartAutoplay();
        }
    }


    /* =============================================
       HAMBURGER MENU
    ============================================= */

    const hamburger = document.getElementById("hamburgerBtn");
    const navLinks  = document.getElementById("navLinks");
    const navClose  = document.getElementById("navClose");
    const overlay   = document.getElementById("navOverlay");

    function openMenu() {
        navLinks.classList.add("open");
        overlay.classList.add("active");
        hamburger.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
    }

    function closeMenu() {
        navLinks.classList.remove("open");
        overlay.classList.remove("active");
        hamburger.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
    }

    if (hamburger) {
        hamburger.addEventListener("click", () => {
            navLinks.classList.contains("open") ? closeMenu() : openMenu();
        });

        navClose.addEventListener("click", closeMenu);
        overlay.addEventListener("click", closeMenu);

        navLinks.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", closeMenu);
        });
    }

});


/* =============================================
   PAGE TRANSITIONS — prefetch + exit fade
   - Hover: prefetch inmediato del destino
   - Click: fade-out 80ms antes de navegar
     (solo en browsers sin View Transitions API,
      donde la API lo maneja nativa y automáticamente)
============================================= */

(function () {
    const isInternal = (href) => {
        if (!href) return false;
        if (/^(#|javascript:|mailto:|tel:)/.test(href)) return false;
        try {
            return new URL(href, location.href).origin === location.origin;
        } catch { return false; }
    };

    /* ── Prefetch on hover ── */
    const prefetched = new Set();
    document.addEventListener('mouseover', (e) => {
        const a = e.target.closest('a[href]');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!isInternal(href) || prefetched.has(href)) return;
        prefetched.add(href);
        const link = document.createElement('link');
        link.rel  = 'prefetch';
        link.href = href;
        document.head.appendChild(link);
    }, { passive: true });

    /* ── Exit animation on click (fallback: no View Transitions) ── */
    if (!document.startViewTransition) {
        document.addEventListener('click', (e) => {
            const a = e.target.closest('a[href]');
            if (!a || a.target === '_blank') return;
            const href = a.getAttribute('href');
            if (!isInternal(href)) return;
            e.preventDefault();
            document.body.style.transition = 'opacity 0.08s ease, transform 0.08s ease';
            document.body.style.opacity   = '0';
            document.body.style.transform = 'translateY(-4px)';
            setTimeout(() => { location.href = href; }, 80);
        });
    }
}());


/* =============================================
   NOVEDADES SLIDER
   - Muestra grupos de 3 posts
   - Dots generados dinámicamente por blade
   - Click en dot navega al grupo correspondiente
   - Solo activo si hay más de 1 grupo
============================================= */

document.addEventListener("DOMContentLoaded", () => {

    const novedades = document.querySelector(".novedades");
    if (!novedades) return;

    const slides = novedades.querySelectorAll(".novedades__slide");
    const dots   = novedades.querySelectorAll(".novedades__dot");

    if (slides.length <= 1) return;

    let current = 0;

    function novedadesGoTo(index) {
        slides[current].classList.remove("active");
        if (dots[current]) dots[current].classList.remove("active");

        current = (index + slides.length) % slides.length;

        slides[current].classList.add("active");
        if (dots[current]) dots[current].classList.add("active");
    }

    dots.forEach((dot, i) => {
        dot.addEventListener("click", () => {
            if (i !== current) novedadesGoTo(i);
        });
    });

});
