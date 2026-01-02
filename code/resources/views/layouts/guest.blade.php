<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" class="light" data-header-styles="light" data-menu-styles="light" loader="true">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Valex - Tailwind Admin Template </title>
    <meta name="description" content="A Tailwind CSS admin template is a pre-designed web page for an admin dashboard. Optimizing it for SEO includes using meta descriptions and ensuring it's responsive and fast-loading.">
    <meta name="keywords" content="dashboard,admin dashboard,template dashboard,html,html dashboard,admin dashboard template,admin template,tailwind ui,admin panel,html and css,html admin template,tailwind framework,html css javascript,tailwind css dashboard,dashboard html css,admin,template admin panel,dashboard html template">
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
        <aside class="app-sidebar" id="sidebar">

            <!-- Start::main-sidebar-header -->
            <div class="main-sidebar-header">
                <a href="/" class="header-logo">
                    <img src="/assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                    <img src="/assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
                    <img src="/assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                    <img src="/assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
                    <img src="/assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
                </a>
            </div>
            <!-- End::main-sidebar-header -->

            <!-- Start::main-sidebar -->
            <div class="main-sidebar" id="sidebar-scroll">

                <!-- Start::nav -->
                <nav class="main-menu-container nav nav-pills flex-column sub-open">
                    <div class="slide-left" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24"
                            height="24" viewBox="0 0 24 24">
                            <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                        </svg></div>
                    <ul class="main-menu">
                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">Main</span></li>
                        <!-- End::slide__category -->

                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
                                <span class="side-menu__label">Dashboard</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Dashboard</a>
                                </li>
                                <li class="slide">
                                    <a href="index.html" class="side-menu__item">Sales</a>
                                </li>
                                <li class="slide">
                                    <a href="index1.html" class="side-menu__item">Ecommerce</a>
                                </li>
                                <li class="slide">
                                    <a href="index2.html" class="side-menu__item">Crm</a>
                                </li>
                                <li class="slide">
                                    <a href="index3.html" class="side-menu__item">Crypto</a>
                                </li>
                                <li class="slide">
                                    <a href="index4.html" class="side-menu__item">NFT</a>
                                </li>
                                <li class="slide">
                                    <a href="index5.html" class="side-menu__item">Analytics</a>
                                </li>
                                <li class="slide">
                                    <a href="index6.html" class="side-menu__item">HRM</a>
                                </li>
                                <li class="slide">
                                    <a href="index7.html" class="side-menu__item">Projects</a>
                                </li>
                                <li class="slide">
                                    <a href="index8.html" class="side-menu__item">jobs</a>
                                </li>
                                <li class="slide">
                                    <a href="index9.html" class="side-menu__item">Stocks</a>
                                </li>
                                <li class="slide">
                                    <a href="index10.html" class="side-menu__item">Course</a>
                                </li>
                                <li class="slide">
                                    <a href="index11.html" class="side-menu__item">Personal</a>
                                </li>
                            </ul>
                        </li>

                        <!-- <li class="slide">
                            <a href="index.html" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h4v6H5zm10 8h4v6h-4zM5 17h4v2H5zM15 5h4v2h-4z" opacity=".3"/><path d="M3 13h8V3H3v10zm2-8h4v6H5V5zm8 16h8V11h-8v10zm2-8h4v6h-4v-6zM13 3v6h8V3h-8zm6 4h-4V5h4v2zM3 21h8v-6H3v6zm2-4h4v2H5v-2z"/></svg>
                                <span class="side-menu__label">Index</span><span class="badge bg-success ms-auto text-left menu-badge !text-white">1</span>
                            </a>
                        </li> -->
                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">General</span></li>
                        <!-- End::slide__category -->

                        <!-- Start::slide -->
                        <li class="slide">
                            <a href="icons.html" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"  viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4c-4.42 0-8 3.58-8 8s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm3.5 4c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5zm-7 0c.83 0 1.5.67 1.5 1.5S9.33 11 8.5 11 7 10.33 7 9.5 7.67 8 8.5 8zm3.5 9.5c-2.33 0-4.32-1.45-5.12-3.5h1.67c.7 1.19 1.97 2 3.45 2s2.76-.81 3.45-2h1.67c-.8 2.05-2.79 3.5-5.12 3.5z" opacity=".3"/><circle cx="15.5" cy="9.5" r="1.5"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="M12 16c-1.48 0-2.75-.81-3.45-2H6.88c.8 2.05 2.79 3.5 5.12 3.5s4.32-1.45 5.12-3.5h-1.67c-.69 1.19-1.97 2-3.45 2zm-.01-14C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>
                                <span class="side-menu__label">Icons</span>
                            </a>
                        </li>
                        <!-- End::slide --> 

                         <!-- Start::slide -->
                         <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 5H5v14h14V5zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" opacity=".3"/><path d="M3 5v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2zm2 0h14v14H5V5zm2 5h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/></svg>
                                <span class="side-menu__label">Charts</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Charts</a>
                                </li>
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">Apex Charts
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="apex-line-charts.html" class="side-menu__item">Line Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-area-charts.html" class="side-menu__item">Area Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-column-charts.html" class="side-menu__item">Column Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-bar-charts.html" class="side-menu__item">Bar Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-mixed-charts.html" class="side-menu__item">Mixed Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-rangearea-charts.html" class="side-menu__item">Range Area Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-timeline-charts.html" class="side-menu__item">Timeline Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-candlestick-charts.html" class="side-menu__item">CandleStick
                                                Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-boxplot-charts.html" class="side-menu__item">Boxplot Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-bubble-charts.html" class="side-menu__item">Bubble Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-scatter-charts.html" class="side-menu__item">Scatter Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-heatmap-charts.html" class="side-menu__item">Heatmap Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-treemap-charts.html" class="side-menu__item">Treemap Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-pie-charts.html" class="side-menu__item">Pie Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-radialbar-charts.html" class="side-menu__item">Radialbar Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-radar-charts.html" class="side-menu__item">Radar Charts</a>
                                        </li>
                                        <li class="slide">
                                            <a href="apex-polararea-charts.html" class="side-menu__item">Polararea Charts</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="slide">
                                    <a href="chartjs.html" class="side-menu__item">Chartjs Charts</a>
                                </li>
                                <li class="slide">
                                    <a href="echartjs.html" class="side-menu__item">Echart Charts</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">Web Apps</span></li>
                        <!-- End::slide__category -->

                         <!-- Start::slide -->
                         <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M4 12c0 4.08 3.06 7.44 7 7.93V4.07C7.05 4.56 4 7.92 4 12z" opacity=".3"/><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93v15.86zm2-15.86c1.03.13 2 .45 2.87.93H13v-.93zM13 7h5.24c.25.31.48.65.68 1H13V7zm0 3h6.74c.08.33.15.66.19 1H13v-1zm0 9.93V19h2.87c-.87.48-1.84.8-2.87.93zM18.24 17H13v-1h5.92c-.2.35-.43.69-.68 1zm1.5-3H13v-1h6.93c-.04.34-.11.67-.19 1z"/></svg>
                                <span class="side-menu__label">Apps</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Apps</a>
                                </li>
                                <li class="slide">
                                    <a href="cards.html" class="side-menu__item">Cards</a>
                                </li>
                                <li class="slide">
                                    <a href="draggable.html" class="side-menu__item">Draggable Cards</a>
                                </li>
                                <li class="slide">
                                    <a href="full-calendar.html" class="side-menu__item">Calendar</a>
                                </li>
                                <li class="slide">
                                    <a href="contacts.html" class="side-menu__item">Contacts</a>
                                </li>
                                <li class="slide">
                                    <a href="notifications.html" class="side-menu__item">Notifications</a>
                                </li>
                                <li class="slide">
                                    <a href="widgets.html" class="side-menu__item">Widgets</a>
                                </li>
                                <li class="slide">
                                    <a href="treeview.html" class="side-menu__item">Treeview</a>
                                </li>
                                <li class="slide">
                                    <a href="file-manager.html" class="side-menu__item">File Manager</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M6.26 9L12 13.47 17.74 9 12 4.53z" opacity=".3"/><path d="M19.37 12.8l-7.38 5.74-7.37-5.73L3 14.07l9 7 9-7zM12 2L3 9l1.63 1.27L12 16l7.36-5.73L21 9l-9-7zm0 11.47L6.26 9 12 4.53 17.74 9 12 13.47z"/></svg>
                                <span class="side-menu__label">Elements</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1 mega-menu">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Elements</a>
                                </li>
                                <li class="slide">
                                    <a href="alerts.html" class="side-menu__item">Alerts</a>
                                </li>
                                <li class="slide">
                                    <a href="avatars.html" class="side-menu__item">Avatar</a>
                                </li>
                                <li class="slide">
                                    <a href="breadcrumb.html" class="side-menu__item">Breadcrumb</a>
                                </li>
                                <li class="slide">
                                    <a href="buttons.html" class="side-menu__item">Buttons</a>
                                </li>
                                <li class="slide">
                                    <a href="buttongroup.html" class="side-menu__item">Button Group</a>
                                </li>
                                <li class="slide">
                                    <a href="badge.html" class="side-menu__item">Badge</a>
                                </li>
                                <li class="slide">
                                    <a href="dropdowns.html" class="side-menu__item">Dropdown</a>
                                </li>
                                <li class="slide">
                                    <a href="list.html" class="side-menu__item">List</a>
                                </li>
                                <li class="slide">
                                    <a href="listgroup.html" class="side-menu__item">List Group</a>
                                </li>
                                <li class="slide">
                                    <a href="blockquotes.html" class="side-menu__item">Blockquotes</a>
                                </li>
                                <li class="slide">
                                    <a href="navbar.html" class="side-menu__item">Navbar</a>
                                </li>
                                <li class="slide">
                                    <a href="images_figures.html" class="side-menu__item">Images & Figures</a>
                                </li>
                                <li class="slide">
                                    <a href="pagination.html" class="side-menu__item">Pagination</a>
                                </li>
                                <li class="slide">
                                    <a href="popovers.html" class="side-menu__item">Popovers</a>
                                </li>
                                <li class="slide">
                                    <a href="progress.html" class="side-menu__item">Progress</a>
                                </li>
                                <li class="slide">
                                    <a href="spinners.html" class="side-menu__item">Spinners</a>
                                </li>
                                <li class="slide">
                                    <a href="typography.html" class="side-menu__item">Typography</a>
                                </li>
                                <li class="slide">
                                    <a href="tooltips.html" class="side-menu__item">Tooltips</a>
                                </li>
                                <li class="slide">
                                    <a href="toasts.html" class="side-menu__item">Toasts</a>
                                </li>
                                <li class="slide">
                                    <a href="navs-tabs.html" class="side-menu__item">Navs & Tabs</a>
                                </li>
                                <li class="slide">
                                    <a href="scrollspy.html" class="side-menu__item">Scrollspy</a>
                                </li>
                                <li class="slide">
                                    <a href="object-fit.html" class="side-menu__item">Object Fit</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8c.28 0 .5-.22.5-.5 0-.16-.08-.28-.14-.35-.41-.46-.63-1.05-.63-1.65 0-1.38 1.12-2.5 2.5-2.5H16c2.21 0 4-1.79 4-4 0-3.86-3.59-7-8-7zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 10 6.5 10s1.5.67 1.5 1.5S7.33 13 6.5 13zm3-4C8.67 9 8 8.33 8 7.5S8.67 6 9.5 6s1.5.67 1.5 1.5S10.33 9 9.5 9zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 6 14.5 6s1.5.67 1.5 1.5S15.33 9 14.5 9zm4.5 2.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5.67-1.5 1.5-1.5 1.5.67 1.5 1.5z" opacity=".3"/><path d="M12 2C6.49 2 2 6.49 2 12s4.49 10 10 10c1.38 0 2.5-1.12 2.5-2.5 0-.61-.23-1.21-.64-1.67-.08-.09-.13-.21-.13-.33 0-.28.22-.5.5-.5H16c3.31 0 6-2.69 6-6 0-4.96-4.49-9-10-9zm4 13h-1.77c-1.38 0-2.5 1.12-2.5 2.5 0 .61.22 1.19.63 1.65.06.07.14.19.14.35 0 .28-.22.5-.5.5-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.14 8 7c0 2.21-1.79 4-4 4z"/><circle cx="6.5" cy="11.5" r="1.5"/><circle cx="9.5" cy="7.5" r="1.5"/><circle cx="14.5" cy="7.5" r="1.5"/><circle cx="17.5" cy="11.5" r="1.5"/></svg>
                                <span class="side-menu__label">Advanced Ui</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Advanced Ui</a>
                                </li>
                                <li class="slide">
                                    <a href="accordions_collpase.html" class="side-menu__item">Accordions</a>
                                </li>
                                <li class="slide">
                                    <a href="modals_closes.html" class="side-menu__item">Modals</a>
                                </li>
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">Timeline
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="timeline.html" class="side-menu__item">Timeline-1</a>
                                        </li>
                                        <li class="slide">
                                            <a href="timeline2.html" class="side-menu__item">Timeline-2</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="slide">
                                    <a href="stepper.html" class="side-menu__item">Stepper</a>
                                </li>
                                <li class="slide">
                                    <a href="Indicators.html" class="side-menu__item">Indicators</a>
                                </li>
                                <li class="slide">
                                    <a href="form_range.html" class="side-menu__item">Range Slider</a>
                                </li>
                                <li class="slide">
                                    <a href="sweet-alerts.html" class="side-menu__item">Sweet Alerts</a>
                                </li>
                                <li class="slide">
                                    <a href="ratings.html" class="side-menu__item">Ratings</a>
                                </li>
                                <li class="slide">
                                    <a href="search.html" class="side-menu__item">Search</a>
                                </li>
                                <li class="slide">
                                    <a href="userlist.html" class="side-menu__item">Userlist</a>
                                </li>
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">Blog
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="blog.html" class="side-menu__item">Blog</a>
                                        </li>
                                        <li class="slide">
                                            <a href="blog-details.html" class="side-menu__item">Blog Details</a>
                                        </li>
                                        <li class="slide">
                                            <a href="blog-create.html" class="side-menu__item">Blog Post</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="slide">
                                    <a href="offcanvas.html" class="side-menu__item">Offcanvas</a>
                                </li>
                                <li class="slide">
                                    <a href="placeholders.html" class="side-menu__item">Skeleton</a>
                                </li>
                                <li class="slide">
                                    <a href="swiperjs.html" class="side-menu__item">Swiper JS</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide__category -->
                        <li class="slide__category"><span class="category-name">Multi Levels</span></li>
                        <!-- End::slide__category -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 9h14V5H5v4zm2-3.5c.83 0 1.5.67 1.5 1.5S7.83 8.5 7 8.5 5.5 7.83 5.5 7 6.17 5.5 7 5.5zM5 19h14v-4H5v4zm2-3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5-1.5-.67-1.5-1.5.67-1.5 1.5-1.5z" opacity=".3"/><path d="M20 13H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zm-1 6H5v-4h14v4zm-12-.5c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5-1.5.67-1.5 1.5.67 1.5 1.5 1.5zM20 3H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zm-1 6H5V5h14v4zM7 8.5c.83 0 1.5-.67 1.5-1.5S7.83 5.5 7 5.5 5.5 6.17 5.5 7 6.17 8.5 7 8.5z"/></svg>
                                <span class="side-menu__label">Menu Levels</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Menu Levels</a>
                                </li>
                                <li class="slide">
                                    <a href="javascript:void(0);" class="side-menu__item">Level-1</a>
                                </li>
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">Level-2
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="javascript:void(0);" class="side-menu__item">Level-2-1</a>
                                        </li>
                                        <li class="slide has-sub">
                                            <a href="javascript:void(0);" class="side-menu__item">Level-2-2
                                                <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                            <ul class="slide-menu child3">
                                                <li class="slide">
                                                    <a href="javascript:void(0);" class="side-menu__item">Level-2-2-1</a>
                                                </li>
                                                <li class="slide">
                                                    <a href="javascript:void(0);" class="side-menu__item">Level-2-2-2</a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->
 
                             <!-- Start::slide__category -->
                             <li class="slide__category"><span class="category-name">Pages</span></li>
                             <!-- End::slide__category -->
                              <!-- Start::slide -->
                            <li class="slide has-sub">
                                <a href="javascript:void(0);" class="side-menu__item">
                                    <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" class="side-menu__icon" viewBox="0 0 24 24" ><g></g><g><g/><g><path d="M21,5c-1.11-0.35-2.33-0.5-3.5-0.5c-1.95,0-4.05,0.4-5.5,1.5c-1.45-1.1-3.55-1.5-5.5-1.5S2.45,4.9,1,6v14.65 c0,0.25,0.25,0.5,0.5,0.5c0.1,0,0.15-0.05,0.25-0.05C3.1,20.45,5.05,20,6.5,20c1.95,0,4.05,0.4,5.5,1.5c1.35-0.85,3.8-1.5,5.5-1.5 c1.65,0,3.35,0.3,4.75,1.05c0.1,0.05,0.15,0.05,0.25,0.05c0.25,0,0.5-0.25,0.5-0.5V6C22.4,5.55,21.75,5.25,21,5z M3,18.5V7 c1.1-0.35,2.3-0.5,3.5-0.5c1.34,0,3.13,0.41,4.5,0.99v11.5C9.63,18.41,7.84,18,6.5,18C5.3,18,4.1,18.15,3,18.5z M21,18.5 c-1.1-0.35-2.3-0.5-3.5-0.5c-1.34,0-3.13,0.41-4.5,0.99V7.49c1.37-0.59,3.16-0.99,4.5-0.99c1.2,0,2.4,0.15,3.5,0.5V18.5z"/><path d="M11,7.49C9.63,6.91,7.84,6.5,6.5,6.5C5.3,6.5,4.1,6.65,3,7v11.5C4.1,18.15,5.3,18,6.5,18 c1.34,0,3.13,0.41,4.5,0.99V7.49z" opacity=".3"/></g><g><path d="M17.5,10.5c0.88,0,1.73,0.09,2.5,0.26V9.24C19.21,9.09,18.36,9,17.5,9c-1.28,0-2.46,0.16-3.5,0.47v1.57 C14.99,10.69,16.18,10.5,17.5,10.5z"/><path d="M17.5,13.16c0.88,0,1.73,0.09,2.5,0.26V11.9c-0.79-0.15-1.64-0.24-2.5-0.24c-1.28,0-2.46,0.16-3.5,0.47v1.57 C14.99,13.36,16.18,13.16,17.5,13.16z"/><path d="M17.5,15.83c0.88,0,1.73,0.09,2.5,0.26v-1.52c-0.79-0.15-1.64-0.24-2.5-0.24c-1.28,0-2.46,0.16-3.5,0.47v1.57 C14.99,16.02,16.18,15.83,17.5,15.83z"/></g></g></svg>
                                    <span class="side-menu__label">Pages</span>
                                    <i class="fe fe-chevron-right side-menu__angle"></i>
                                </a>
                                <ul class="slide-menu child1">
                                    <li class="slide side-menu__label1">
                                        <a href="javascript:void(0);">Pages</a>
                                    </li>
                                    <li class="slide">
                                        <a href="profile.html" class="side-menu__item">Profile</a>
                                    </li>
                                    <li class="slide">
                                        <a href="about-us.html" class="side-menu__item">About-Us</a>
                                    </li>
                                    <li class="slide">
                                        <a href="reviews.html" class="side-menu__item">Review</a>
                                    </li>
                                    <li class="slide">
                                        <a href="team.html" class="side-menu__item">Team</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Invoice
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="create-invoice.html" class="side-menu__item">Create Invoice</a>
                                            </li>
                                            <li class="slide">
                                                <a href="invoice-details.html" class="side-menu__item">Invoice Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="invoice-list.html" class="side-menu__item">Invoice List</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide">
                                        <a href="pricing.html" class="side-menu__item">Pricing</a>
                                    </li>
                                    <li class="slide">
                                        <a href="gallery.html" class="side-menu__item">Gallery</a>
                                    </li>
                                    <li class="slide">
                                        <a href="todolist.html" class="side-menu__item">TodoList</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Task
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="task-kanban-board.html" class="side-menu__item">Kanban Board</a>
                                            </li>
                                            <li class="slide">
                                                <a href="task-list-view.html" class="side-menu__item">List View</a>
                                            </li>
                                            <li class="slide">
                                                <a href="task-details.html" class="side-menu__item">Task Details</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide">
                                        <a href="faqs.html" class="side-menu__item">Faqs</a>
                                    </li>
                                    <li class="slide">
                                        <a href="empty.html" class="side-menu__item">Empty</a>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Mail
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="mail.html" class="side-menu__item">Mail</a>
                                            </li>
                                            <li class="slide">
                                                <a href="mail-settings.html" class="side-menu__item">Mail Settings</a>
                                            </li>
                                            <li class="slide">
                                                <a href="chat.html" class="side-menu__item">Chat</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Ecommerce
                                            <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="products.html" class="side-menu__item">Products</a>
                                            </li>
                                            <li class="slide">
                                                <a href="products-list.html" class="side-menu__item">Products List</a>
                                            </li>
                                            <li class="slide">
                                                <a href="add-products.html" class="side-menu__item">Add Products</a>
                                            </li>
                                            <li class="slide">
                                                <a href="edit-products.html" class="side-menu__item">Edit Products</a>
                                            </li>
                                            <li class="slide">
                                                <a href="product-details.html" class="side-menu__item">Product Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="orders.html" class="side-menu__item">Orders</a>
                                            </li>
                                            <li class="slide">
                                                <a href="order-details.html" class="side-menu__item">Order Details</a>
                                            </li>
                                            <li class="slide">
                                                <a href="product-cart.html" class="side-menu__item">Cart</a>
                                            </li>
                                            <li class="slide">
                                                <a href="check-out.html" class="side-menu__item">Check-out</a>
                                            </li>
                                            <li class="slide">
                                                <a href="wish-list.html" class="side-menu__item">Wish List</a>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="slide has-sub">
                                        <a href="javascript:void(0);" class="side-menu__item">Custom Pages
                                            <i class="fe fe-chevron-right side-menu__angle"></i>
                                        </a>
                                        <ul class="slide-menu child2">
                                            <li class="slide">
                                                <a href="comingsoon.html" class="side-menu__item">Coming Soon</a>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Create Password
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="create-password-basic.html" class="side-menu__item">Basic</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="create-password-cover.html" class="side-menu__item">Cover</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Sign In
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="sign-in-basic.html" class="side-menu__item">Basic</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="sign-in-cover.html" class="side-menu__item">Cover</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Sign Up
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="sign-up-basic.html" class="side-menu__item">Basic</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="sign-up-cover.html" class="side-menu__item">Cover</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Reset Password
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="reset-password-basic.html" class="side-menu__item">Basic</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="reset-password-cover.html" class="side-menu__item">Cover</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Lockscreen
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="lockscreen-basic.html" class="side-menu__item">Basic</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="lockscreen-cover.html" class="side-menu__item">Cover</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Two Step Verification
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="two-step-verfication-basic.html" class="side-menu__item">Basic</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="two-step-verfication-cover.html" class="side-menu__item">Cover</a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="slide">
                                                <a href="maintanace.html" class="side-menu__item">Under Maintenance</a>
                                            </li>
                                            <li class="slide has-sub">
                                                <a href="javascript:void(0);" class="side-menu__item">Error
                                                    <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                                <ul class="slide-menu child3">
                                                    <li class="slide">
                                                        <a href="404-error.html" class="side-menu__item">404 Error</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="500-error.html" class="side-menu__item">500 Error</a>
                                                    </li>
                                                    <li class="slide">
                                                        <a href="501-error.html" class="side-menu__item">501 Error</a>
                                                    </li>
                                                </ul>
                                            </li>
                                    
                                        </ul>
                                    </li>
                                    <li class="slide">
                                        <a href="terms.html" class="side-menu__item">Terms &amp; Conditions</a>
                                    </li>
                                </ul>
                            </li>
                            <!-- End::slide -->

                         <!-- Start::slide__category -->
                         <li class="slide__category"><span class="category-name">Components</span></li>
                         <!-- End::slide__category -->
                         <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M13 4H6v16h12V9h-5V4zm3 14H8v-2h8v2zm0-6v2H8v-2h8z" opacity=".3"/><path d="M8 16h8v2H8zm0-4h8v2H8zm6-10H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg>
                                <span class="side-menu__label">Forms</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Forms</a>
                                </li>
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">Form Elements
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="form-inputs.html" class="side-menu__item">Inputs</a>
                                        </li>
                                        <li class="slide">
                                            <a href="form_check_radios.html" class="side-menu__item">Checks & Radios</a>
                                        </li>
                                        <li class="slide">
                                            <a href="form_input_group.html" class="side-menu__item">Input Group</a>
                                        </li>
                                        <li class="slide">
                                            <a href="form_select.html" class="side-menu__item">Form Select</a>
                                        </li>
                                        <li class="slide">
                                            <a href="form_file_uploads.html" class="side-menu__item">File Uploads</a>
                                        </li>
                                        <li class="slide">
                                            <a href="form_dateTime_pickers.html" class="side-menu__item">Date,Time Picker</a>
                                        </li>
                                        <li class="slide">
                                            <a href="form_color_pickers.html" class="side-menu__item">Color Pickers</a>
                                        </li>
                                        <li class="slide">
                                            <a href="advanced-select.html" class="side-menu__item">Advanced Select</a>
                                        </li>
                                        <li class="slide">
                                            <a href="inputnumber.html" class="side-menu__item">Input Number</a>
                                        </li>
                                        <li class="slide">
                                            <a href="passwords.html" class="side-menu__item">Passwords</a>
                                        </li>
                                        <li class="slide">
                                            <a href="counters.html" class="side-menu__item">Counters &amp; Markup</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="slide">
                                    <a href="form_layout.html" class="side-menu__item">Form Layouts</a>
                                </li>
                                <li class="slide has-sub">
                                    <a href="javascript:void(0);" class="side-menu__item">Form Editors
                                        <i class="fe fe-chevron-right side-menu__angle"></i></a>
                                    <ul class="slide-menu child2">
                                        <li class="slide">
                                            <a href="quill_editor.html" class="side-menu__item">Quill Editor</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="slide">
                                    <a href="form_validation.html" class="side-menu__item">Validation</a>
                                </li>
                                <li class="slide">
                                    <a href="form_select2.html" class="side-menu__item">Select2</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M5 5h15v3H5zm12 5h3v9h-3zm-7 0h5v9h-5zm-5 0h3v9H5z" opacity=".3"/><path d="M20 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h15c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM8 19H5v-9h3v9zm7 0h-5v-9h5v9zm5 0h-3v-9h3v9zm0-11H5V5h15v3z"/></svg>
                                <span class="side-menu__label">Tables</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Tables</a>
                                </li>
                                <li class="slide">
                                    <a href="tables.html" class="side-menu__item">Tables</a>
                                </li>
                                <li class="slide">
                                    <a href="grid-tables.html" class="side-menu__item">Grid JS Tables</a>
                                </li>
                                <li class="slide">
                                    <a href="data-tables.html" class="side-menu__item">Data Tables</a>
                                </li>
                                <li class="slide">
                                    <a href="edittable.html" class="side-menu__item">Edit Table</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide">
                            <a href="landing.html" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0z" fill="none"/><path d="M12 4.02C7.6 4.02 4.02 7.6 4.02 12S7.6 19.98 12 19.98s7.98-3.58 7.98-7.98S16.4 4.02 12 4.02zM11.39 19v-5.5H8.25l4.5-8.5v5.5h3L11.39 19z" opacity=".3"/><path d="M12 2.02c-5.51 0-9.98 4.47-9.98 9.98s4.47 9.98 9.98 9.98 9.98-4.47 9.98-9.98S17.51 2.02 12 2.02zm0 17.96c-4.4 0-7.98-3.58-7.98-7.98S7.6 4.02 12 4.02 19.98 7.6 19.98 12 16.4 19.98 12 19.98zM12.75 5l-4.5 8.5h3.14V19l4.36-8.5h-3V5z"/></svg>
                                <span class="side-menu__label">Landing Page</span>
                            </a>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 4C9.24 4 7 6.24 7 9c0 2.85 2.92 7.21 5 9.88 2.11-2.69 5-7 5-9.88 0-2.76-2.24-5-5-5zm0 7.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" opacity=".3"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zM7 9c0-2.76 2.24-5 5-5s5 2.24 5 5c0 2.88-2.88 7.19-5 9.88C9.92 16.21 7 11.85 7 9z"/><circle cx="12" cy="9" r="2.5"/></svg>
                                <span class="side-menu__label">Maps</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Maps</a>
                                </li>
                                <li class="slide">
                                    <a href="google-maps.html" class="side-menu__item">Google Maps</a>
                                </li>
                                <li class="slide">
                                    <a href="leaflet-maps.html" class="side-menu__item">Leaflet Maps</a>
                                </li>
                                <li class="slide">
                                    <a href="vector-maps.html" class="side-menu__item">Vector Maps</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->

                        <!-- Start::slide -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 24 24" ><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10.9 19.91c.36.05.72.09 1.1.09 2.18 0 4.16-.88 5.61-2.3L14.89 13l-3.99 6.91zm-1.04-.21l2.71-4.7H4.59c.93 2.28 2.87 4.03 5.27 4.7zM8.54 12L5.7 7.09C4.64 8.45 4 10.15 4 12c0 .69.1 1.36.26 2h5.43l-1.15-2zm9.76 4.91C19.36 15.55 20 13.85 20 12c0-.69-.1-1.36-.26-2h-5.43l3.99 6.91zM13.73 9h5.68c-.93-2.28-2.88-4.04-5.28-4.7L11.42 9h2.31zm-3.46 0l2.83-4.92C12.74 4.03 12.37 4 12 4c-2.18 0-4.16.88-5.6 2.3L9.12 11l1.15-2z" opacity=".3"/><path d="M12 22c5.52 0 10-4.48 10-10 0-4.75-3.31-8.72-7.75-9.74l-.08-.04-.01.02C13.46 2.09 12.74 2 12 2 6.48 2 2 6.48 2 12s4.48 10 10 10zm0-2c-.38 0-.74-.04-1.1-.09L14.89 13l2.72 4.7C16.16 19.12 14.18 20 12 20zm8-8c0 1.85-.64 3.55-1.7 4.91l-4-6.91h5.43c.17.64.27 1.31.27 2zm-.59-3h-7.99l2.71-4.7c2.4.66 4.35 2.42 5.28 4.7zM12 4c.37 0 .74.03 1.1.08L10.27 9l-1.15 2L6.4 6.3C7.84 4.88 9.82 4 12 4zm-8 8c0-1.85.64-3.55 1.7-4.91L8.54 12l1.15 2H4.26C4.1 13.36 4 12.69 4 12zm6.27 3h2.3l-2.71 4.7c-2.4-.67-4.35-2.42-5.28-4.7h5.69z"/></svg>
                                <span class="side-menu__label">Utilities</span>
                                <i class="fe fe-chevron-right side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child1">
                                <li class="slide side-menu__label1">
                                    <a href="javascript:void(0);">Utilities</a>
                                </li>
                                <li class="slide">
                                    <a href="borders.html" class="side-menu__item">Borders</a>
                                </li>
                                <li class="slide">
                                    <a href="colors.html" class="side-menu__item">Colors</a>
                                </li>
                                <li class="slide">
                                    <a href="columns.html" class="side-menu__item">Columns</a>
                                </li>
                                <li class="slide">
                                    <a href="flex.html" class="side-menu__item">Flex</a>
                                </li>
                                <li class="slide">
                                    <a href="grids.html" class="side-menu__item">Grid</a>
                                </li>
                            </ul>
                        </li>
                        <!-- End::slide -->
                    </ul>
                    <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24"> <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path> </svg></div>
                </nav>
                <!-- End::nav -->

            </div>
            <!-- End::main-sidebar -->

        </aside>
        <!-- End::app-sidebar -->

        @yield('content')
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
        <footer class="footer mt-auto xl:ps-[15rem]  font-normal font-inter bg-white  leading-normal !text-[0.875rem] shadow-[0_0_0.4rem_rgba(0,0,0,0.1)] dark:bg-bodybg py-4 text-center">
            <div class="container">
              <span class="text-gray dark:text-defaulttextcolor/50"><a
                href="javascript:void(0);" class="text-defaulttextcolor font-semibold dark:text-defaulttextcolor">Ristopilot</a>.
                  Developed with <span class="bi bi-heart-fill text-danger"></span> by <a href="javascript:void(0);">
                      <span class="font-semibold text-primary underline">Mediator</span>
                  </a>
              </span>
            </div>
        </footer>
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

    @livewireScripts
</body>

</html>


