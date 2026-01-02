<!DOCTYPE html>
<html lang="it">
<head>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="max-image-preview:large">
    <title>{{config('app.name')}} Management</title>
    @yield('meta')
    <link rel="icon" type="image/png" href="/favicon.ico">
    <meta name="author" content="{{config('app.name')}}" />
    
</head>

<body class="antialiased bg-bordeaux">
    <header class="mb-20">
        @include('components.menu.header.navbar')
    </header>
        @auth
            @include('components.menu.sidenav.sidenav')
        @endauth
    @auth
    <div class="p-4 sm:ml-64">
    @endauth
        @yield('content')
    @auth
    </div>
    @endauth
</body>




</html>
