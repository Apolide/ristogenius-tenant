<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ rtrim(config('app.url'), '/') }}/assets/css/style.css">
</head>
<body class="h-screen overflow-hidden bg-bodybg">
    <main class="h-screen w-full overflow-hidden">{{ $slot }}</main>
</body>
</html>
