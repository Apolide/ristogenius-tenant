<!doctype html>
<html lang="{{ app(\App\Services\CustomerLanguageService::class)->htmlLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <title>{{ $title ?? 'Menu' }}</title>
    @vite(
    // 'resources/assets/css/style.css'
    'resources/assets/scss/style.scss'
    )

    @stack('styles')
    @livewireStyles</head>
<body class="bg-[#f8f6f1] text-[#20312e] antialiased"><main>{{ $slot }}</main>@livewireScripts</body>
</html>
