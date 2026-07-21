<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0f766e"><title>{{ $title ?? 'Menu' }}</title><link rel="stylesheet" href="/assets/css/style.min.css">@livewireStyles</head>
<body class="bg-[#f8f6f1] text-[#20312e] antialiased"><main>{{ $slot }}</main>@livewireScripts</body>
</html>
