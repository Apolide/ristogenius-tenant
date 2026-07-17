<!DOCTYPE html>
<html lang="it" dir="ltr" data-nav-layout="vertical" class="light" data-header-styles="light" data-menu-styles="light" loader="true">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ristopilot Bin Telegram Operator</title>
    {{-- <meta name="description" content="A Tailwind CSS admin template is a pre-designed web page for an admin dashboard. Optimizing it for SEO includes using meta descriptions and ensuring it's responsive and fast-loading.">
    <meta name="keywords" content="dashboard,admin dashboard,template dashboard,html,html dashboard,admin dashboard template,admin template,tailwind ui,admin panel,html and css,html admin template,tailwind framework,html css javascript,tailwind css dashboard,dashboard html css,admin,template admin panel,dashboard html template"> --}}
    <meta property="og:image" content="https://ristopilot.com/assets/images/brand-logos/ristopilot.jpg">
    <!-- Favicon -->
    <link rel="shortcut icon" href="/assets/images/brand-logos/favicon.ico">

    <!-- Main JS -->
    <script src="/assets/js/main.js"></script>

    <!-- Style Css -->
    <link rel="stylesheet" href="/assets/css/style.min.css">

    <!-- Simplebar Css -->
    <link rel="stylesheet" href="/assets/libs/simplebar/simplebar.min.css">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">
    @livewireStyles
    <style>
      input[type="text"],
      input[type="number"],
      input[type="email"],
      input[type="tel"],
      input[type="password"],
      textarea {
        font-size: 16px !important;
      }
      </style>
</head>

<body>

    
   
    
    <!-- Loader -->
    <div class="page">

      <header class="app-header">
        <nav class="main-header" aria-label="Global">
          <div class="main-header-container !px-[0.85rem] py-4">

            <div class="header-content-left">
              <!-- Start::header-element -->
              <div class="header-element">
               
              
                  <img src="/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
    
            
              
              </div>
              <!-- End::header-element -->

              <!-- End::header-element -->
              <div class="header-element !items-center">
                <!-- Start::header-link -->
                <a aria-label="Hide Sidebar"
                  class="sidemenu-toggle animated-arrow header-link  hor-toggle horizontal-navtoggle inline-flex items-center"
                  href="javascript:void(0);"></a>
                  <?php /*
                <div class="main-header-center hidden lg:block">
                  <input
                    class="form-control placeholder:!text-headerprimecolor placeholder:opacity-70 placeholder:font-thin placeholder:text-sm"
                    placeholder="Search for anything..." type="search">
                  <button class="btn"><i class="fa fa-search hidden md:block opacity-[0.5]"></i></button>
                </div>
                */ ?>
                <!-- End::header-link -->
              </div>
            </div>
          </div>
        </nav>
      </header>
 

        @yield('content')
        <!-- Start::content  -->
        <!-- End::content-->


        
        
        <!-- Footer Start -->
        @include('layouts.footer.base')
        <!-- Footer End -->

    </div>

    <!-- Back To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="ri-arrow-up-s-fill text-xl"></i></span>
    </div>

    <div id="responsive-overlay"></div>


    <!-- Switch JS -->
    <script src="/assets/js/switch.js"></script>


    <!-- Simplebar JS -->
    <script src="/assets/libs/simplebar/simplebar.min.js"></script>

    <!-- Preline JS -->
    <script src="/assets/libs/preline/preline.js"></script>


    @livewireScripts
</body>

</html>

