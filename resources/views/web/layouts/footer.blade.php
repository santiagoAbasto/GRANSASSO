@php
/** @var \App\Models\SiteSetting|null $settings */
$settings = \App\Models\SiteSetting::first();
$whatsappPage = \App\Models\Page::where('slug', 'site-whatsapp')
    ->with(['sections' => fn ($q) => $q->where('type', 'whatsapp_float')->limit(1)])
    ->first();
$whatsappContent = is_array($whatsappPage?->sections->first()?->content)
    ? $whatsappPage->sections->first()->content
    : [];

// Dirección: el campo es JSON multiidioma
$_address = is_array($settings?->address)
    ? ($settings->address[app()->getLocale()] ?? $settings->address['es'] ?? '')
    : ($settings?->address ?? '');

// Emails para el footer
$_emailSales      = $settings?->email_sales ?? '';
$_emailCommercial = $settings?->email_general ?? '';

// Teléfono principal
$_phone = $settings?->phone_1 ?? '';
$_whatsappNumber = trim((string) ($whatsappContent['number'] ?? ''));
$_whatsappName = trim((string) ($whatsappContent['name'] ?? $settings?->company_name ?? ''));
$_whatsappDigits = preg_replace('/\D/', '', $_whatsappNumber);

if ($_whatsappDigits === '' && $_phone) {
    $_whatsappDigits = preg_replace('/\D/', '', $_phone);
}

$_whatsappUrl = $_whatsappDigits !== '' ? 'https://wa.me/' . $_whatsappDigits : null;

// Links de navegación del footer
$_linksLeft = [
    ['title' => __('ui.nav.empresa'),     'url' => route('empresa')],
    ['title' => __('ui.nav.products'),    'url' => route('products.index')],
    ['title' => __('ui.nav.special_dev'), 'url' => route('desarrollos')],
];
$_linksRight = [
    ['title' => __('ui.nav.quality'),  'url' => route('calidad')],
    ['title' => __('ui.nav.contact'),  'url' => route('contacto')],
];
@endphp

<footer class="footer">

    <div class="footer__main">
        <div class="footer__container">

            {{-- LOGO --}}
            <div class="footer__logo-col">
                <a href="{{ route('home') }}" class="footer__logo-wrap">
                    <img src="{{ asset('storage/images/logos/group.svg') }}" alt="Gran Sasso" class="footer__logo-img">
                </a>
            </div>

            {{-- SECCIONES --}}
            <div class="footer__col footer__secciones">
                <h4 class="footer__title">{{ __('ui.footer.sections') }}</h4>
                <div class="footer__links-grid">
                    <ul class="footer__links-list">
                        @foreach($_linksLeft as $link)
                        <li><a href="{{ $link['url'] }}" class="footer__link">{{ $link['title'] }}</a></li>
                        @endforeach
                    </ul>
                    <ul class="footer__links-list">
                        @foreach($_linksRight as $link)
                        <li><a href="{{ $link['url'] }}" class="footer__link">{{ $link['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- NEWSLETTER + REDES SOCIALES --}}
            <div class="footer__col footer__newsletter-col">
                <h4 class="footer__title">{{ __('ui.footer.newsletter_title') }}</h4>
                <form class="footer__newsletter" id="newsletterForm"
                      action="{{ route('newsletter.store') }}" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="{{ __('ui.footer.newsletter_placeholder') }}" class="footer__newsletter-input" required>
                    <button type="submit" class="footer__newsletter-btn" aria-label="Suscribirse">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <path d="M9 18L15 12L9 6" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
                <p class="footer__newsletter-msg" id="newsletterMsg" aria-live="polite"></p>

                @if($settings?->facebook || $settings?->instagram || $settings?->linkedin)
                <div class="footer__social">
                    <h4 class="footer__title">{{ __('ui.footer.social') }}</h4>
                    <div class="footer__social-links">
                        @if($settings?->facebook)
                        <a href="{{ $settings->facebook }}" target="_blank" rel="noopener noreferrer" class="footer__social-link" aria-label="Facebook">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M18 2H15C13.6739 2 12.4021 2.52678 11.4645 3.46447C10.5268 4.40215 10 5.67392 10 7V10H7V14H10V22H14V14H17L18 10H14V7C14 6.73478 14.1054 6.48043 14.2929 6.29289C14.4804 6.10536 14.7348 6 15 6H18V2Z" fill="white"/>
                            </svg>
                        </a>
                        @endif
                        @if($settings?->instagram)
                        <a href="{{ $settings->instagram }}" target="_blank" rel="noopener noreferrer" class="footer__social-link" aria-label="Instagram">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                        </a>
                        @endif
                        @if($settings?->linkedin)
                        <a href="{{ $settings->linkedin }}" target="_blank" rel="noopener noreferrer" class="footer__social-link" aria-label="LinkedIn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
                @endif

            </div>

            {{-- CONTACTO --}}
            <div class="footer__col footer__contact-col">
                <h4 class="footer__title">{{ __('ui.footer.contact') }}</h4>

                @if($_address)
                <a href="https://maps.google.com/?q={{ urlencode($_address) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="footer__contact-row footer__contact-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="footer__contact-icon" aria-hidden="true">
                        <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 2C9.87827 2 7.84344 2.84285 6.34315 4.34315C4.84285 5.84344 4 7.87827 4 10C4 11.892 4.402 13.13 5.5 14.5L12 22L18.5 14.5C19.598 13.13 20 11.892 20 10C20 7.87827 19.1571 5.84344 17.6569 4.34315C16.1566 2.84285 14.1217 2 12 2Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    @php
                        $addrParts = explode(', ', $_address, 2);
                    @endphp
                    <span class="footer__contact-text">{{ $addrParts[0] }},<br>{{ $addrParts[1] ?? '' }}</span>
                </a>
                @endif

                @if($settings?->phone_1 || $settings?->phone_2 || $settings?->phone_3)
                <div class="footer__contact-row footer__contact-row--phones">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="footer__contact-icon" aria-hidden="true">
                        <path d="M13.832 16.568C14.0385 16.6628 14.2712 16.6845 14.4917 16.6294C14.7122 16.5744 14.9073 16.4458 15.045 16.265L15.4 15.8C15.5863 15.5516 15.8279 15.35 16.1056 15.2111C16.3833 15.0723 16.6895 15 17 15H20C20.5304 15 21.0391 15.2107 21.4142 15.5858C21.7893 15.9609 22 16.4696 22 17V20C22 20.5304 21.7893 21.0391 21.4142 21.4142C21.0391 21.7893 20.5304 22 20 22C15.2261 22 10.6477 20.1036 7.27208 16.7279C3.89642 13.3523 2 8.7739 2 4C2 3.46957 2.21071 2.96086 2.58579 2.58579C2.96086 2.21071 3.46957 2 4 2H7C7.53043 2 8.03914 2.21071 8.41421 2.58579C8.78929 2.96086 9 3.46957 9 4V7C9 7.31049 8.92771 7.61672 8.78885 7.89443C8.65 8.17214 8.44839 8.41371 8.2 8.6L7.732 8.951C7.54842 9.09118 7.41902 9.29059 7.36579 9.51535C7.31256 9.74012 7.33878 9.97638 7.44 10.184C8.80668 12.9599 11.0544 15.2048 13.832 16.568Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="footer__contact-text">
                        @if($settings?->phone_1){{ $settings->phone_1 }}@endif
                        @if($settings?->phone_2) | {{ $settings->phone_2 }}@endif
                        @if($settings?->phone_3) | {{ $settings->phone_3 }}@endif
                    </span>
                </div>
                @endif

                @if($_emailSales)
                <div class="footer__contact-row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" class="footer__contact-icon" aria-hidden="true">
                        <path d="M22 7L13.009 12.727C12.7039 12.9042 12.3573 12.9976 12.0045 12.9976C11.6517 12.9976 11.3051 12.9042 11 12.727L2 7M4 4H20C21.1046 4 22 4.89543 22 6V18C22 19.1046 21.1046 20 20 20H4C2.89543 20 2 19.1046 2 18V6C2 4.89543 2.89543 4 4 4Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="footer__contact-text">
                        <a href="mailto:{{ $_emailSales }}" class="footer__contact-link">{{ $_emailSales }}</a>
                        @if($_emailCommercial)
                        <br><a href="mailto:{{ $_emailCommercial }}" class="footer__contact-link">{{ $_emailCommercial }}</a>
                        @endif
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>

    {{-- COPYRIGHT --}}
    <div class="footer__bottom">
        <div class="footer__bottom-inner">
            <span class="footer__copyright">{{ __('ui.footer.copyright', ['year' => date('Y')]) }}</span>
            <span class="footer__credit">By <a href="https://osole.com.ar/" target="_blank"><strong>Osole</strong></a></span>
        </div>
    </div>

    {{-- WHATSAPP FLOTANTE --}}
    @if($_whatsappUrl)
    <a href="{{ $_whatsappUrl }}"
       target="_blank" rel="noopener noreferrer"
       class="whatsapp-float"
       aria-label="{{ trim(__('ui.footer.whatsapp_label') . ' ' . $_whatsappName) }}"
       title="{{ $_whatsappName ?: __('ui.footer.whatsapp_label') }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M14.5823 11.985C14.3328 11.8608 13.1095 11.2625 12.8817 11.1792C12.6539 11.0967 12.4881 11.0558 12.3215 11.3042C12.1557 11.5508 11.6793 12.1092 11.5344 12.2742C11.3887 12.44 11.2438 12.46 10.9952 12.3367C10.7465 12.2117 9.94429 11.9508 8.99391 11.1075C8.25453 10.4509 7.75464 9.64002 7.60978 9.39169C7.46492 9.14419 7.59387 9.01002 7.71863 8.88669C7.83084 8.77585 7.96732 8.59752 8.09209 8.45335C8.21685 8.30835 8.25788 8.20502 8.34078 8.03919C8.42451 7.87419 8.38265 7.73002 8.31985 7.60585C8.25788 7.48169 7.7605 6.26252 7.55284 5.76669C7.35104 5.28419 7.14589 5.35003 6.99349 5.34169C6.8478 5.33503 6.682 5.33336 6.51621 5.33336C6.35041 5.33336 6.08079 5.39503 5.85303 5.64336C5.62444 5.89086 4.98219 6.49002 4.98219 7.70919C4.98219 8.92752 5.87313 10.105 5.99789 10.2709C6.12266 10.4359 7.75213 12.9375 10.2482 14.01C10.8428 14.265 11.3058 14.4175 11.6667 14.5308C12.2629 14.72 12.8055 14.6933 13.2342 14.6292C13.7115 14.5583 14.7063 14.03 14.9139 13.4517C15.1208 12.8733 15.1207 12.3775 15.0588 12.2742C14.9968 12.1708 14.8319 12.1092 14.5823 11.985ZM10.0423 18.1542H10.0389C8.55634 18.1544 7.10099 17.7578 5.8254 17.0058L5.52396 16.8275L2.39062 17.6458L3.22712 14.6058L3.03035 14.2942C2.20149 12.9811 1.76286 11.4615 1.76512 9.91085C1.7668 5.36919 5.47958 1.6742 10.0456 1.6742C12.2562 1.6742 14.3345 2.53253 15.897 4.08919C16.6676 4.85301 17.2785 5.76133 17.6941 6.7616C18.1098 7.76188 18.322 8.83425 18.3186 9.91668C18.3169 14.4583 14.6041 18.1542 10.0423 18.1542ZM17.086 2.9067C16.1634 1.98247 15.0657 1.24965 13.8564 0.7507C12.6472 0.251754 11.3505 -0.00339687 10.0414 3.41479e-05C4.55347 3.41479e-05 0.0854091 4.44586 0.0837344 9.91002C0.0811914 11.649 0.539563 13.3578 1.4126 14.8642L0 20L5.27861 18.6217C6.73884 19.4134 8.37519 19.8283 10.0381 19.8283H10.0423C15.5302 19.8283 19.9983 15.3825 20 9.91752C20.004 8.61525 19.7485 7.32511 19.2484 6.12172C18.7482 4.91833 18.0132 3.82559 17.086 2.9067Z" fill="white"/>
        </svg>
    </a>
    @endif

</footer>
