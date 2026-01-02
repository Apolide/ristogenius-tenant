<header class="app-header">

    <div class="main-header-container container-fluid">

        <div class="header-content-left">

            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="/" class="header-logo">
                        <img src="/assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
                        <img src="/assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
                    </a>
                </div>
            </div>

            <div class="header-element">
                <a aria-label="anchor" href="javascript:void(0);" class="sidemenu-toggle header-link">
                    <span class="open-toggle">
                        <i class="ri-menu-3-line text-xl"></i>
                    </span>
                </a>
            </div>

        </div>

        <div class="header-content-right">

            <div class="header-element !items-center">
                <div class="lg:hidden block">
                    <a href="#contact" class="ti-btn ti-btn-primary !m-0">
                        Contattaci
                    </a>
                </div>
            </div>

        </div>

    </div>

</header>

<aside class="app-sidebar sticky !top-0" id="sidebar">
    <div class="container-xl !p-0">
        <div class="main-sidebar">
            <nav class="main-menu-container nav nav-pills flex-column sub-open">
                <div class="landing-logo-container my-auto hidden lg:block">
                    <div class="responsive-logo">
                        <a class="responsive-logo-light" href="/" aria-label="Brand"><img src="/assets/images/brand-logos/desktop-logo.png" alt="logo"></a>
                        <a class="responsive-logo-dark" href="/" aria-label="Brand"><img src="/assets/images/brand-logos/desktop-full-white.png" alt="logo"></a>
                    </div>
                </div>
                <div class="slide-left hidden" id="slide-left">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                    </svg>
                </div>
                <ul class="main-menu">
                    <li class="slide">
                        <a class="side-menu__item" href="https://ristopilot.com">
                            <span class="side-menu__label">Chi siamo</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a class="side-menu__item" href="{{route('customer.profile')}}">
                            <span class="side-menu__label">Profilo</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="{{route('customer.coupons')}}" class="side-menu__item">
                            <span class="side-menu__label">Coupons</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="{{route('customer.fidelity')}}" class="side-menu__item">
                            <span class="side-menu__label">Le tue fidelity card</span>
                        </a>
                    </li>

                    {{-- <li class="slide">
                        <a href="/#faq" class="side-menu__item">
                            <span class="side-menu__label">FAQ</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="/#about" class="side-menu__item">
                            <span class="side-menu__label">Chi siamo</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a href="/#contact" class="side-menu__item">
                            <span class="side-menu__label">Contatti</span>
                        </a>
                    </li>
                    <li class="slide has-sub open">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <span class="side-menu__label me-2">Strumenti</span>
                            <i class="fe fe-chevron-right side-menu__angle op-8"></i>
                        </a>
                        <ul class="slide-menu child1 active" data-popper-placement="bottom"
                            style="position: relative; left: 0px; top: 0px; margin: 0px; box-sizing: border-box; transform: translate3d(410.5px, 39px, 0px); display: block;">
                            <li class="slide">
                                <a href="/prenotazioni-ristoranti" class="side-menu__item">Prenotazioni</a>
                            </li>
                            <li class="slide">
                                <a href="/marketing-ristoranti" class="side-menu__item">Marketing</a>
                            </li>
    
                        </ul>
                    </li> --}}
                </ul>

                <div class="slide-right hidden" id="slide-right">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                        <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z">
                        </path>
                    </svg>
                </div>
                <div class="lg:flex hidden space-x-2 rtl:space-x-reverse">
                    <a href="@yield('contact-btn-href', '#contact')" class="ti-btn w-[6.375rem] ti-btn-primary-full m-0 p-2">Contattaci</a>
                </div>
            </nav>

        </div>
    </div>
</aside>
