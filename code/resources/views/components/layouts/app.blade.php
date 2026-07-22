<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" class="light" data-header-styles="light" data-menu-styles="light" loader="true">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Page Title' }}</title>
    {{-- <meta name="description" content="A Tailwind CSS admin template is a pre-designed web page for an admin dashboard. Optimizing it for SEO includes using meta descriptions and ensuring it's responsive and fast-loading.">
    <meta name="keywords" content="dashboard,admin dashboard,template dashboard,html,html dashboard,admin dashboard template,admin template,tailwind ui,admin panel,html and css,html admin template,tailwind framework,html css javascript,tailwind css dashboard,dashboard html css,admin,template admin panel,dashboard html template"> --}}

    <!-- Favicon -->
    <link rel="shortcut icon" href="/assets/images/brand-logos/favicon.ico">

    <!-- Main JS -->
    @if (request()->is('manage') || request()->is('manage/*'))
    <script src="/assets/js/manage-theme.js"></script>
    @endif
    <script src="/assets/js/main.js"></script>

    <!-- Style Css -->
    {{-- <link rel="stylesheet" href="/assets/css/style.css"> --}}
    @vite([
        'resources/assets/scss/style.scss',
    'resources/assets/css/icons.css',
    ])

    <!-- Simplebar Css -->
    <link rel="stylesheet" href="/assets/libs/simplebar/simplebar.min.css">

    <!-- Color Picker Css -->
    <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">

    <!-- Notifications Css -->
    <link rel="stylesheet" href="/assets/libs/awesome-notifications/style.css">

    @stack('head-scripts')
    @livewireStyles
</head>

<body>

    
   
    <!-- Loader -->
    <div id="loader" >
        <img src="/assets/images/media/loader.svg" alt="">
    </div>
    <!-- Loader -->
    <div class="page">
        <!-- Start::Header -->
        @include('layouts.header.base')
        <!-- End::Header -->

        <!-- Start::Off-canvas sidebar-->
        <div id="hs-overlay-chat" class="hs-overlay hidden ti-offcanvas ti-offcanvas-right overflow-auto" tabindex="-1">
          <div class="ti-offcanvas-header !py-2 rounded-none">
            <h5 class="text-[.875rem] uppercase mb-0 text-defaulttextcolor font-semibold" id="sidebarLabel">Notifications</h5>
            <button type="button"
              class="ti-btn flex-shrink-0 p-0  transition-none text-defaulttextcolor dark:text-defaulttextcolor/70 hover:text-gray-700 focus:ring-gray-400 focus:ring-offset-white  dark:hover:text-white/80 dark:focus:ring-white/10 dark:focus:ring-offset-white/10"
              data-hs-overlay="#hs-overlay-chat">
              <span class="sr-only">Close modal</span>
              <i class="ri-close-fill leading-none text-lg"></i>
            </button>
          </div>
          <div class="ti-offcanvas-body rounded-none p-0">
            <ul class="nav nav-tabs  p-4" role="tablist">
              <div class=" rtl:space-x-reverse" aria-label="Tabs" role="tablist" role="tablist">
                <button type="button"
                  class="hs-tab-active:bg-primary w-full mb-2 rounded-[4px] !py-[10px] !px-[16px] text-start hs-tab-active:border-b-transparent text-defaultsize border-0 hs-tab-active:text-white dark:hs-tab-active:bg-primary dark:hs-tab-active:border-b-white/10 dark:hs-tab-active:text-white  bg-light  font-semibold  text-defaulttextcolor dark:text-defaulttextcolor/70  hover:text-gray-700 dark:bg-bodybg2 dark:border-white/10  active"
                  id="chat-item" data-hs-tab="#chat" aria-controls="chat" role="tab">
                  <i class="fe fe-message-circle text-[.9375rem] me-2 inline-flex"></i>Chat
                </button>
                <button type="button"
                  class="hs-tab-active:bg-primary w-full mb-2  rounded-[4px] !py-[10px] !px-[16px] text-start hs-tab-active:border-b-transparent text-defaultsize border-0 hs-tab-active:text-white dark:hs-tab-active:bg-primary dark:hs-tab-active:border-b-white/10 dark:hs-tab-active:text-white   bg-light font-semibold  text-defaulttextcolor dark:text-defaulttextcolor/70  hover:text-gray-700 dark:bg-bodybg2 dark:border-white/10  dark:hover:text-gray-300"
                  id="notification-item" data-hs-tab="#notification" aria-controls="notification" role="tab">
                  <i class="fe fe-bell text-[.9375rem] me-2 inline-flex"></i> Notifications
                </button>
                <button type="button"
                  class="hs-tab-active:bg-primary w-full mb-0 rounded-[4px] !py-[10px] !px-[16px] text-start hs-tab-active:border-b-transparent text-defaultsize border-0 hs-tab-active:text-white dark:hs-tab-active:bg-primary dark:hs-tab-active:border-b-white/10 dark:hs-tab-active:text-white   bg-light font-semibold  text-defaulttextcolor dark:text-defaulttextcolor/70  hover:text-gray-700 dark:bg-bodybg2 dark:border-white/10  dark:hover:text-gray-300"
                  id="friends-item" data-hs-tab="#friends" aria-controls="friends" role="tab">
                  <i class="fe fe-users text-[.9375rem] me-2 inline-flex"></i>Friends
                </button>
              </div>
            </ul>
            <div class="tab-content !border-0 ">
              <div
                class="tab-pane !text-defaulttextcolor dark:text-defaulttextcolor/70 !border-s-0 !border-e-0 !rounded-none !p-0 show border-defaultborder dark:border-defaultborder/10 "
                id="chat" role="tabpanel" aria-labelledby="chat-item">
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-primary avatar-rounded avatar-md">CH</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>New Websites is Created</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">30 mins ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-danger avatar-rounded avatar-md">N</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Prepare For the Next Project</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">2 hours ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-info avatar-rounded avatar-md">S</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Decide the live Discussion</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">3 hours ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-warning avatar-rounded avatar-md">K</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Meeting at 3:00 pm</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">4 hours ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-success avatar-rounded avatar-md">R</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Prepare for Presentation</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">1 day ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-pinkmain avatar-rounded avatar-md">MS</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Prepare for Presentation</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">1 day ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex items-center border-b border-defaultborder dark:border-defaultborder/10  p-3">
                  <div class="">
                    <span class="avatar bg-purplemain avatar-rounded avatar-md">L</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Prepare for Presentation</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">45 minutes ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="list flex border-b border-defaultborder dark:border-defaultborder/10 items-center p-3">
                  <div class="">
                    <span class="avatar bg-indigomain avatar-rounded avatar-md">U</span>
                  </div>
                  <a class="w-full ms-3" href="javascript:void(0);">
                    <p class="mb-0 flex ">
                      <b>Prepare for Presentation</b>
                    </p>
                    <div class="flex justify-between items-center">
                      <div class="flex items-center">
                        <i class="fa-regular fa-clock text-textmuted me-1 text-[.6875rem]"></i>
                        <small class="text-textmuted ms-auto">2 days ago</small>
                        <p class="mb-0"></p>
                      </div>
                    </div>
                  </a>
                </div>
              </div>
              <div
                class="tab-pane !text-defaulttextcolor dark:text-defaulttextcolor/70 !border-s-0 !border-e-0 !rounded-none !p-0 border-defaultborder dark:border-defaultborder/10  hidden"
                id="notification" role="tabpanel" aria-labelledby="notification-item">
                <div class="ti-list-group ti-list-group-flush ">
                  <div class="ti-list-group-item !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/1.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Madeleine</strong> Hey! there I' am available....
                      <div class="small text-textmuted">
                        3 hours ago
                      </div>
                    </div>
                  </div>
                  <div class="ti-list-group-item !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/2.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Anthony</strong> New product Launching...
                      <div class="small text-textmuted">
                        5 hour ago
                      </div>
                    </div>
                  </div>
                  <div class="ti-list-group-item !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/3.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Olivia</strong> New Schedule Realease......
                      <div class="small text-textmuted">
                        45 minutes ago
                      </div>
                    </div>
                  </div>
                  <div class="ti-list-group-item !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/4.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Madeleine</strong> Hey! there I' am available....
                      <div class="small text-textmuted">
                        3 hours ago
                      </div>
                    </div>
                  </div>
                  <div class="ti-list-group-item !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/5.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Anthony</strong> New product Launching...
                      <div class="small text-textmuted">
                        5 hour ago
                      </div>
                    </div>
                  </div>
                  <div class="ti-list-group-item !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/6.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Olivia</strong> New Schedule Realease......
                      <div class="small text-textmuted">
                        45 minutes ago
                      </div>
                    </div>
                  </div>
                  <div
                    class="ti-list-group-item  !border-b border-defaultborder dark:border-defaultborder/10 !border-s-0 !border-e-0 !border-t-0 flex  items-center">
                    <span class="avatar avatar-lg avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/7.jpg" alt="img">
                    </span>
                    <div class="ms-3">
                      <strong>Olivia</strong> Hey! there I' am available....
                      <div class="small text-textmuted">
                        12 minutes ago
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div
                class="tab-pane !text-defaulttextcolor dark:text-defaulttextcolor/70 !border-s-0 !border-e-0 !rounded-none !p-0 border-defaultborder dark:border-defaultborder/10  active hidden"
                id="friends" role="tabpanel" aria-labelledby="friends-item">
                <div class="ti-list-group ti-list-group-flush ">
                  <div class="ti-list-group-item flex !border-t-0 items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/1.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Mozelle Belt</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/2.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Florinda Carasco</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/5.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Alina Bernier</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/6.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Zula Mclaughin</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/8.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Isidro Heide</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/8.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Mozelle Belt</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/9.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Florinda Carasco</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/10.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Alina Bernier</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/11.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Zula Mclaughin</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/12.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Isidro Heide</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/2.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Florinda Carasco</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/2.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Alina Bernier</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex  items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/3.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Zula Mclaughin</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                  <div class="ti-list-group-item flex !border-b border-defaultborder dark:border-defaultborder/10 items-center">
                    <span class="avatar avatar-md online avatar-rounded flex-shrink-0">
                      <img src="/assets/images/faces/4.jpg" alt="img">
                    </span>
                    <div class="ms-2">
                      <div class="font-semibold" data-hs-overlay="#chatmodel">Isidro Heide</div>
                    </div>
                    <div class="ms-auto">
                      <a href="javascript:void(0);" class="ti-btn ti-btn-sm ti-btn-light" data-hs-overlay="#chatmodel"><i
                          class="fab fa-facebook-messenger"></i></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- End::Off-canvas sidebar-->

        <!--chat-modal-->
        <div class="hs-overlay hidden ti-modal" id="chatmodel">
          <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content chat !border-0">
              <div class="box overflow-hidden !mb-0 !border-0 !shadow-none">
                <div class="action-header  flex items-center clearfix">
                  <div class="float-start xs:hidden flex">
                    <div class="avatar avatar-lg rounded-circle me-3"> <img src="/assets/images/faces/6.jpg"
                        class="rounded-circle user_img" alt="img"> </div>
                    <div class="items-center">
                      <h5 class="text-fixed-white mb-0">Daneil Scott</h5> <span class="dot-label bg-success"></span><span
                        class="me-3 text-fixed-white">online</span>
                    </div>
                  </div>
                  <ul class="ah-actions actions ms-auto items-center float-end">
                    <li class="call-icon"> <a href="#" class="hidden md:block phone-button" data-hs-overlay="#audiomodal"> <i
                          class="fe fe-phone"></i> </a> </li>
                    <li class="video-icon"> <a href="#" class="hidden md:block phone-button" data-hs-overlay="#videomodal"> <i
                          class="fe fe-video"></i> </a> </li>
                    <li class="hs-dropdown ti-dropdown">
                      <a href="#" data-bs-toggle="dropdown" aria-expanded="true"> <i class="fe fe-more-vertical"></i> </a>
                      <ul class="ti-dropdown-menu hs-dropdown-menu dropdown-menu-end hidden">
                        <li class="ti-dropdown-item"><i class="fa fa-user-circle"></i> View profile</li>
                        <li class="ti-dropdown-item"><i class="fa fa-users"></i>Add friends</li>
                        <li class="ti-dropdown-item"><i class="fa fa-plus"></i> Add to group</li>
                        <li class="ti-dropdown-item"><i class="fa fa-ban"></i> Block</li>
                      </ul>
                    </li>
                    <li> <a href="" class="" data-bs-dismiss="modal" aria-label="Close"> <i
                          class="fe fe-x-circle text-fixed-white"></i> </a> </li>
                  </ul>
                </div>
                <div class="box-body msg_card_body">
                  <div class="chat-box-single-line"> <abbr
                      class="timestamp !text-defaulttextcolor dark:!text-defaulttextcolor/70">February 1st, 2019</abbr> </div>
                  <div class="flex justify-start">
                    <div class="img_cont_msg"> <img src="/assets/images/faces/6.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                    <div class="msg_cotainer"> Hi, how are you Jenna Side? <span class="msg_time">8:40 AM, Today</span> </div>
                  </div>
                  <div class="flex justify-end ">
                    <div class="msg_cotainer_send"> Hi Connor Paige i am good tnx how about you? <span
                        class="msg_time_send">8:55 AM, Today</span> </div>
                    <div class="img_cont_msg"> <img src="/assets/images/faces/9.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                  </div>
                  <div class="flex justify-start ">
                    <div class="img_cont_msg"> <img src="/assets/images/faces/6.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                    <div class="msg_cotainer"> I am good too, thank you for your chat template <span class="msg_time">9:00 AM,
                        Today</span> </div>
                  </div>
                  <div class="flex justify-end ">
                    <div class="msg_cotainer_send"> You welcome Connor Paige <span class="msg_time_send">9:05 AM, Today</span>
                    </div>
                    <div class="img_cont_msg"> <img src="/assets/images/faces/9.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                  </div>
                  <div class="flex justify-start ">
                    <div class="img_cont_msg"> <img src="/assets/images/faces/6.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                    <div class="msg_cotainer"> Yo, Can you update Views? <span class="msg_time">9:07 AM, Today</span> </div>
                  </div>
                  <div class="flex justify-end mb-4">
                    <div class="msg_cotainer_send"> But I must explain to you how all this mistaken born and I will give <span
                        class="msg_time_send">9:10 AM, Today</span> </div>
                    <div class="img_cont_msg"> <img src="/assets/images/faces/9.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                  </div>
                  <div class="flex justify-start ">
                    <div class="img_cont_msg"> <img src="/assets/images/faces/6.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                    <div class="msg_cotainer"> Yo, Can you update Views? <span class="msg_time">9:07 AM, Today</span> </div>
                  </div>
                  <div class="flex justify-end mb-4">
                    <div class="msg_cotainer_send"> But I must explain to you how all this mistaken born and I will give <span
                        class="msg_time_send">9:10 AM, Today</span> </div>
                    <div class="img_cont_msg"> <img src="/assets/images/faces/9.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                  </div>
                  <div class="flex justify-start ">
                    <div class="img_cont_msg"> <img src="/assets/images/faces/6.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                    <div class="msg_cotainer"> Yo, Can you update Views? <span class="msg_time">9:07 AM, Today</span> </div>
                  </div>
                  <div class="flex justify-end mb-4">
                    <div class="msg_cotainer_send"> But I must explain to you how all this mistaken born and I will give <span
                        class="msg_time_send">9:10 AM, Today</span> </div>
                    <div class="img_cont_msg"> <img src="/assets/images/faces/9.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                  </div>
                  <div class="flex justify-start">
                    <div class="img_cont_msg"> <img src="/assets/images/faces/6.jpg" class="rounded-circle user_img_msg"
                        alt="img"> </div>
                    <div class="msg_cotainer"> Okay Bye, text you later.. <span class="msg_time">9:12 AM, Today</span> </div>
                  </div>
                </div>
                <div class="box-footer border-t">
                  <div class="msb-reply flex">
                    <div class="input-group"> <input type="text" class="form-control " placeholder="Typing...."> <button
                        type="button" class="ti-btn ti-btn-primary-full !mb-0"> <i class="far fa-paper-plane"
                          aria-hidden="true"></i> </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--chat-modal-->


        <!--Video Modal -->
        <div id="videomodal" class="hs-overlay hidden ti-modal">
          <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content !bg-[#3b4863] !border-0">
              <div class="mx-auto text-center p-[3rem]">
                <button type="button"
                  class="hs-dropdown-toggle relative -end-[226px] -top-[29px] !text-[1.5rem] !font-medium text-white"
                  data-hs-overlay="#videomodal">
                  <span class="sr-only">Close</span>
                  <i class="bi bi-x"></i>
                </button>
                <h5 class="text-white">Valex Video call</h5>
                <img src="/assets/images/faces/6.jpg" class="rounded-full !h-[90px]  mt-4 mb-3 inline-flex" alt="img">
                <h4 class="mb-1 font-semibold text-white">Daneil Scott</h4>
                <h6 class="loading animate-loadingtext text-white">Calling...</h6>
                <div class="mt-[3rem] mb-[2rem]">
                  <div class="grid grid-cols-12 gap-x-4">
                    <div class="col-span-4">
                      <a class="icon icon-shape rounded-full mb-0" href="javascript:void(0);">
                        <i class="fas fa-video-slash"></i>
                      </a>
                    </div>
                    <div class="col-span-4">
                      <a class="icon icon-shape rounded-full text-white mb-0" href="javascript:void(0);"
                        data-hs-overlay="#videomodal">
                        <i class="fas fa-phone !bg-danger !text-white"></i>
                      </a>
                    </div>
                    <div class="col-span-4">
                      <a class="icon icon-shape rounded-full mb-0" href="javascript:void(0);">
                        <i class="fas fa-microphone-slash"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div><!-- modal-body -->
            </div>
          </div><!-- modal-dialog -->
        </div>
        <!--End modal -->

        <!-- Audio Modal -->
        <div id="audiomodal" class="hs-overlay hidden ti-modal">
          <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
            <div class="ti-modal-content border-0">
              <div class="mx-auto text-center p-[3rem]">
                <button type="button"
                  class="hs-dropdown-toggle relative -end-[226px] -top-[29px] !text-[1.5rem] !font-medium text-[#8c9097]"
                  data-hs-overlay="#audiomodal">
                  <span class="sr-only">Close</span>
                  <i class="bi bi-x"></i>
                </button>
                <h6 class="text-defaulttextcolor dark:text-defaulttextcolor/70">Valex Voice call</h6>
                <img src="/assets/images/faces/6.jpg" class="rounded-full !h-[90px] mt-6 mb-4 inline-flex" alt="img">
                <h5 class="mb-1 font-medium text-defaulttextcolor dark:text-defaulttextcolor/70">Daneil Scott</h5>
                <h6 class="loading animate-loadingtext text-defaulttextcolor dark:text-defaulttextcolor/70">Calling...</h6>
                <div class="mt-[2rem] mb-[2rem]">
                  <div class="grid grid-cols-12 gap-x-4">
                    <div class="col-span-4">
                      <a class="icon icon-shape rounded-circle mb-0" href="javascript:void(0);">
                        <i class="fas fa-volume-up !bg-light !text-defaulttextcolor"></i>
                      </a>
                    </div>
                    <div class="col-span-4">
                      <a class="icon icon-shape rounded-circle text-white mb-0" href="javascript:void(0);"
                        data-hs-overlay="#audiomodal">
                        <i class="fas fa-phone text-white !bg-success"></i>
                      </a>
                    </div>
                    <div class="col-span-4">
                      <a class="icon icon-shape  rounded-circle mb-0" href="javascript:void(0);">
                        <i class="fas fa-microphone-slash !bg-light !text-defaulttextcolor"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div><!-- modal-body -->
            </div>
          </div><!-- modal-dialog -->
        </div>
        <!--End modal -->

        <!-- Start::app-sidebar -->
        @include('layouts.sidebar.base')
        <!-- End::app-sidebar -->
        
        {{ $slot }}
        <!-- Start::content  -->
        <!-- End::content-->


        <!-- ========== Search Modal ========== -->
        <div id="search-modal" class="hs-overlay ti-modal hidden mt-[1.75rem]">
          <div class="ti-modal-box">
            <div class="ti-modal-content !border !border-defaultborder dark:!border-defaultborder/10 !rounded-[0.5rem]">
              <div class="ti-modal-body">

                <div class="input-group border-[2px] border-primary rounded-[0.25rem] w-full flex">
                  <a href="javascript:void(0);"
                    class="input-group-text flex items-center bg-light border-e-[#dee2e6] !py-[0.375rem] !px-[0.75rem] !rounded-none !text-[0.875rem]"
                    id="Search-Grid"><i class="fe fe-search header-link-icon text-[0.875rem]"></i></a>

                  <input type="search" class="form-control border-0 px-2 !text-[0.8rem] w-full focus:ring-transparent"
                    placeholder="Search" aria-label="Username">

                  <a href="javascript:void(0);" class="flex items-center input-group-text bg-light !py-[0.375rem] !px-[0.75rem]"
                    id="voice-search"><i class="fe fe-mic header-link-icon"></i></a>
                  <div class="hs-dropdown ti-dropdown">
                    <a href="javascript:void(0);"
                      class="flex items-center hs-dropdown-toggle ti-dropdown-toggle btn btn-light btn-icon !bg-light !py-[0.375rem] !rounded-none !px-[0.75rem] text-[0.95rem] h-[2.413rem] w-[2.313rem]">
                      <i class="fe fe-more-vertical"></i>
                    </a>

                    <ul class="absolute hs-dropdown-menu ti-dropdown-menu !-mt-2 !p-0 hidden">
                      <li><a
                          class="ti-dropdown-item flex text-defaulttextcolor dark:text-defaulttextcolor/70 !py-[0.5rem] !px-[0.9375rem] !text-[0.8125rem] font-[500]"
                          href="#">Action</a></li>
                      <li><a
                          class="ti-dropdown-item flex text-defaulttextcolor dark:text-defaulttextcolor/70 !py-[0.5rem] !px-[0.9375rem] !text-[0.8125rem] font-[500]"
                          href="#">Another action</a></li>
                      <li><a
                          class="ti-dropdown-item flex text-defaulttextcolor dark:text-defaulttextcolor/70 !py-[0.5rem] !px-[0.9375rem] !text-[0.8125rem] font-[500]"
                          href="#">Something else here</a></li>
                      <li>
                        <hr class="dropdown-divider">
                      </li>
                      <li><a
                          class="ti-dropdown-item flex text-defaulttextcolor dark:text-defaulttextcolor/70 !py-[0.5rem] !px-[0.9375rem] !text-[0.8125rem] font-[500]"
                          href="#">Separated link</a></li>
                    </ul>
                  </div>
                </div>
                <div class="mt-5">
                  <p class="font-normal  text-[#8c9097] text-[0.813rem] dark:text-gray-200 mb-2">Are You Looking For...</p>

                  <span class="search-tags text-[0.75rem] !py-[0rem] !px-[0.55rem] dark:border-defaultborder/10"><i class="fe fe-user me-2"></i>People<a
                      href="javascript:void(0)" class="tag-addon header-remove-btn"><span class="sr-only">Remove badge</span><i class="fe fe-x"></i></a></span>
                  <span class="search-tags text-[0.75rem] !py-[0rem] !px-[0.55rem] dark:border-defaultborder/10"><i class="fe fe-file-text me-2"></i>Pages<a
                      href="javascript:void(0)" class="tag-addon header-remove-btn"><span class="sr-only">Remove badge</span><i class="fe fe-x"></i></a></span>
                  <span class="search-tags text-[0.75rem] !py-[0rem] !px-[0.55rem] dark:border-defaultborder/10"><i
                      class="fe fe-align-left me-2"></i>Articles<a href="javascript:void(0)" class="tag-addon header-remove-btn"><span class="sr-only">Remove badge</span><i
                        class="fe fe-x"></i></a></span>
                  <span class="search-tags text-[0.75rem] !py-[0rem] !px-[0.55rem] dark:border-defaultborder/10"><i class="fe fe-server me-2"></i>Tags<a
                      href="javascript:void(0)" class="tag-addon header-remove-btn"><span class="sr-only">Remove badge</span><i class="fe fe-x"></i></a></span>

                </div>


                <div class="my-[1.5rem]">
                  <p class="font-normal  text-[#8c9097] text-[0.813rem] mb-2">Recent Search :</p>

                  <div id="dismiss-alert" role="alert"
                    class="!p-2 border dark:border-defaultborder/10 rounded-[0.3125rem] flex items-center text-defaulttextcolor dark:text-defaulttextcolor/70 !mb-2 !text-[0.8125rem] alert">
                    <a href="notifications.html"><span>Notifications</span></a>
                    <a class="ms-auto leading-none" href="javascript:void(0);" data-hs-remove-element="#dismiss-alert"><i
                        class="fe fe-x !text-[0.8125rem] text-[#8c9097]"></i></a>
                  </div>

                  <div id="dismiss-alert" role="alert"
                    class="!p-2 border dark:border-defaultborder/10 rounded-[0.3125rem] flex items-center text-defaulttextcolor dark:text-defaulttextcolor/70 !mb-2 !text-[0.8125rem] alert">
                    <a href="alerts.html"><span>Alerts</span></a>
                    <a class="ms-auto leading-none" href="javascript:void(0);" data-hs-remove-element="#dismiss-alert"><i
                        class="fe fe-x !text-[0.8125rem] text-[#8c9097]"></i></a>
                  </div>

                  <div id="dismiss-alert" role="alert"
                    class="!p-2 border dark:border-defaultborder/10 rounded-[0.3125rem] flex items-center text-defaulttextcolor dark:text-defaulttextcolor/70 !mb-0 !text-[0.8125rem] alert">
                    <a href="mail.html"><span>Mail</span></a>
                    <a class="ms-auto lh-1" href="javascript:void(0);" data-hs-remove-element="#dismiss-alert"><i
                        class="fe fe-x !text-[0.8125rem] text-[#8c9097]"></i></a>
                  </div>
                </div>
              </div>

              <div class="ti-modal-footer !py-[1rem] !px-[1.25rem]">
                <div class="inline-flex rounded-md  shadow-sm">
                  <button type="button"
                    class="ti-btn-group !px-[0.75rem] !py-[0.45rem]  rounded-s-[0.25rem] !rounded-tr-none !rounded-br-none ti-btn-primary !text-[0.75rem] dark:border-white/10">
                    Search
                  </button>
                  <button type="button"
                    class="ti-btn-group  ti-btn-primary-full rounded-e-[0.25rem] dark:border-white/10 !text-[0.75rem] !rounded-tl-none !rounded-bl-none !px-[0.75rem] !py-[0.45rem]">
                    Clear Recents
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- ========== END Search Modal ========== -->
        
        <!-- Footer Start -->
        @include('layouts.footer.base')
        <!-- Footer End -->

    </div>

    <!-- Back To Top -->
    <div class="scrollToTop">
        <span class="arrow"><i class="ri-arrow-up-s-fill text-xl"></i></span>
    </div>

    <div id="responsive-overlay"></div>


    <!-- popperjs -->
    <script src="/assets/libs/@popperjs/core/umd/popper.min.js"></script>

    <!-- Color Picker JS -->
    <script src="/assets/libs/@simonwep/pickr/pickr.es5.min.js"></script>

    <!-- sidebar JS -->
    <script src="/assets/js/defaultmenu.js"></script>

    <!-- Switch JS -->
    <script src="/assets/js/switch.js"></script>

    <!-- sticky JS -->
    <script src="/assets/js/sticky.js"></script>


    <!-- Simplebar JS -->
    <script src="/assets/libs/simplebar/simplebar.min.js"></script>

    <!-- Preline JS -->
    <script src="/assets/libs/preline/preline.js"></script>

    <!-- Custom JS -->
    <script src="/assets/js/custom.js"></script>

    <script src="/assets/libs/awesome-notifications/index.var.js"></script>
    {{-- <script src="/assets/js/notifications.js"></script> --}}

    {{-- <script src="/js/notifications.js"></script> --}}
  

    @livewireScripts
</body>

</html>
