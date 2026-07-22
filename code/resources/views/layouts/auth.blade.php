<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" class="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Valex - Bootstrap 5 Premium Admin & Dashboard Template </title>
    <meta name="Description" content="Bootstrap Responsive Admin Web Dashboard HTML5 Template">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <!-- Favicon -->
    <link rel="icon" href="/assets/images/brand-logos/favicon.ico" type="image/x-icon">

    <!-- Main Theme Js -->
    <script src="/assets/js/authentication-main.js"></script>
  
    <!-- Style Css -->
    {{-- <link rel="stylesheet" href="/assets/css/style.css"> --}}
    @vite([
        'resources/assets/scss/style.scss',
        'resources/assets/css/icons.css',
    ])

    <!-- Simplebar Css -->
    <link id="style" href="/assets/libs/simplebar/simplebar.min.css" rel="stylesheet">
  
    <!-- Color Picker Css -->
    <link rel="stylesheet" href="/assets/libs/@simonwep/pickr/themes/nano.min.css">
  
    <!-- Swiper Css -->
    <link rel="stylesheet" href="/assets/libs/swiper/swiper-bundle.min.css">


</head>

<body class="bg-white dark:!bg-bodybg">

   
    @yield('content')

    <!-- Show Password JS -->
    <script src="/assets/js/show-password.js"></script>

</body>

</html>
