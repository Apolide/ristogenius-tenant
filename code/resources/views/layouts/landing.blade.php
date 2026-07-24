<!DOCTYPE html>
<html lang="it" class="h-full" dir="ltr" data-nav-layout="horizontal" data-nav-style="menu-click" data-menu-position="fixed">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        {{-- ===== Defaults ===== --}}
        <title>@yield('title', 'Risto Pilot – Gestionale per ristorante con automazione e marketing')</title>
        <meta name="description" content="@yield('meta_description', 'Scopri Risto Pilot, gestionale SAAS per ristoratori. Ottimizza prenotazioni, inventario e vendite per aumentare profitti e semplificare la gestione.')">
        <meta name="keywords" content="@yield('meta_keywords', 'dashboard,admin dashboard,template dashboard,html,html dashboard,admin dashboard template,admin template,tailwind ui,admin panel,html and css,html admin template,tailwind framework,html css javascript,tailwind css dashboard,dashboard html css,admin,template admin panel,dashboard html template')">

        {{-- OG defaults --}}
        <meta property="og:image" content="@yield('og_image', 'https://ristopilot.com/assets/images/brand-logos/ristopilot.jpg')">
        <meta property="og:title" content="@yield('og_title', 'Risto Pilot – Gestionale per ristorante con automazione e marketing')">
        <meta property="og:description" content="@yield('og_description', 'Scopri Risto Pilot, gestionale SAAS per ristoratori.')">

        {{-- (Opzionale) twitter defaults --}}
        <meta name="twitter:card" content="@yield('twitter_card', 'summary_large_image')">
        <meta name="twitter:image" content="@yield('twitter_image', 'https://ristopilot.com/assets/images/brand-logos/ristopilot.jpg')">

        <link rel="icon" href="/assets/images/brand-logos/favicon.ico" type="image/x-icon">

        {{-- ===== Override "secco": se la view pusha qui, sostituisce i defaults ===== --}}
        @stack('meta-override')

        {{-- ===== Extra meta: aggiunte senza sostituire ===== --}}
        @stack('meta')



        @vite([
        // 'resources/assets/css/style.css',
        'resources/assets/scss/style.scss',
        'resources/assets/css/icons.css'
        ])

        <link id="style" href="/assets/libs/simplebar/simplebar.min.css" rel="stylesheet">

        <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">

        <link rel="stylesheet" href="/assets/libs/swiper/swiper-bundle.min.css">

    </head>

    <body class="landing-body">

        <!-- ========== Switcher  ========== -->
        <div id="hs-overlay-switcher" class="hs-overlay hidden ti-offcanvas ti-offcanvas-right" tabindex="-1">
            <div class="ti-offcanvas-header">
                <h5 class="ti-offcanvas-title">
                Selettore
            </h5>
                <button type="button" class="ti-btn flex-shrink-0 p-0 transition-none text-defaulttextcolor dark:text-defaulttextcolor/70 hover:text-gray-700 focus:ring-gray-400 focus:ring-offset-white  dark:hover:text-white/80 dark:focus:ring-white/10 dark:focus:ring-offset-white/10" data-hs-overlay="#hs-overlay-switcher">
                    <span class="sr-only">Chiudi modale</span>
                    <i class="ri-close-circle-line leading-none text-lg"></i>
                </button>
            </div>
            <div class="ti-offcanvas-body" id="switcher-body">
                <div>
                    <div>
                        <p class="switcher-style-head">Modalità colore tema:</p>
                        <div class="grid grid-cols-3 gap-x-6 switcher-style">
                            <div class="flex">
                                <input type="radio" name="theme-style" class="ti-form-radio" id="switcher-light-theme" checked>
                                <label for="switcher-light-theme" class="text-xs text-defaulttextcolor dark:text-defaulttextcolor/70 font-semibold ltr:ml-2 rtl:mr-2 ">Chiaro</label>
                            </div>
                            <div class="flex">
                                <input type="radio" name="theme-style" class="ti-form-radio" id="switcher-dark-theme">
                                <label for="switcher-dark-theme" class="text-xs text-defaulttextcolor dark:text-defaulttextcolor/70 font-semibold ltr:ml-2 rtl:mr-2 ">Scuro</label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="switcher-style-head">Direzioni:</p>
                        <div class="grid grid-cols-3 gap-x-6 switcher-style">
                            <div class="flex">
                                <input type="radio" name="direction" class="ti-form-radio" id="switcher-ltr" checked>
                                <label for="switcher-ltr" class="text-xs font-semibold text-defaulttextcolor dark:text-defaulttextcolor/70 ltr:ml-2 rtl:mr-2 ">LTR</label>
                            </div>
                            <div class="flex">
                                <input type="radio" name="direction" class="ti-form-radio" id="switcher-rtl">
                                <label for="switcher-rtl" class="text-xs font-semibold text-defaulttextcolor dark:text-defaulttextcolor/70 ltr:ml-2 rtl:mr-2 ">RTL</label>
                            </div>
                        </div>
                    </div>
                    <div class="theme-colors">
                        <p class="switcher-style-head">Tema primario:</p>
                        <div class="flex switcher-style space-x-3 rtl:space-x-reverse">
                            <div class="ti-form-radio switch-select">
                                <input class="ti-form-radio color-input color-primary-1" type="radio" name="theme-primary" id="switcher-primary" checked>
                            </div>
                            <div class="ti-form-radio switch-select">
                                <input class="ti-form-radio color-input color-primary-2" type="radio" name="theme-primary" id="switcher-primary1">
                            </div>
                            <div class="ti-form-radio switch-select">
                                <input class="ti-form-radio color-input color-primary-3" type="radio" name="theme-primary" id="switcher-primary2">
                            </div>
                            <div class="ti-form-radio switch-select">
                                <input class="ti-form-radio color-input color-primary-4" type="radio" name="theme-primary" id="switcher-primary3">
                            </div>
                            <div class="ti-form-radio switch-select">
                                <input class="ti-form-radio color-input color-primary-5" type="radio" name="theme-primary" id="switcher-primary4">
                            </div>
                            <div class="ti-form-radio switch-select ltr:pl-0 rtl:pr-0 mt-1 color-primary-light">
                                <div class="theme-container-primary"></div>
                                <div class="pickr-container-primary"></div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="switcher-style-head">Reimposta:</p>
                        <div class="flex justify-center">
                            <a id="reset-all" class="ti-btn ti-btn-danger-full mt-4" href="javascript:void(0);">Reimposta</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <div class="landing-page-wrapper relative">
     
        @yield('content')

        @yield('footer')
    </div>

    <div class="scrollToTop">
        <span class="arrow"><i class="ri-arrow-up-s-fill  text-[1.25rem]"></i></span>
    </div>

    <div id="responsive-overlay"></div>

    <script src="/assets/libs/@popperjs/core/umd/popper.min.js"></script>

    <script src="/assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>

    <script src="/assets/libs/swiper/swiper-bundle.min.js"></script>

    <script src="/assets/js/defaultmenu.js"></script>

    <script src="/assets/js/landing.js"></script>

    <script src="/assets/js/switch.js"></script>

    <script src="/assets/libs/preline/preline.js"></script>

    <script src="/assets/libs/simplebar/simplebar.min.js"></script>

    <script src="/assets/js/sticky.js"></script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-33ZX34SG13"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-33ZX34SG13');
    </script>

</body>

</html>
