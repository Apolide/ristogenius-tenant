<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    {{-- <link rel="stylesheet" href="{{ rtrim(config('app.url'), '/') }}/assets/css/style.css"> --}}
    @vite('resources/assets/scss/style.scss')

    @stack('styles')
    @livewireStyles
</head>
<body class="bg-bodybg min-h-screen">
    <main class="{{ ($fullWidth ?? false) ? 'w-full' : 'max-w-4xl mx-auto px-4 py-8' }}">{{ $slot }}</main>
    @stack('scripts')
    @livewireScripts
</body>
</html>
