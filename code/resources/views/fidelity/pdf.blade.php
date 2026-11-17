<!doctype html>
<html lang="{{ $card->language }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('fidelity.title', [], $card->language) }}</title>
    <style>
        @page { margin: 24pt; }
        body { text-align: center; color: #17223a; font-family: "DejaVu Sans", sans-serif; font-size: 12pt; }
        h1 { font-size: 20pt; margin: 12pt 0 4pt; }
        .logo { max-width: 140pt; max-height: 65pt; margin-bottom: 10pt; }
        .qr { width: 130pt; height: 130pt; }
        a { color: #2355a3; font-size: 11pt; }
    </style>
</head>
<body>
    @if($logo)<img class="logo" src="{{ $logo }}" alt="">@endif
    <div>{{ $brand['name'] }}</div>
    <h1>{{ __('fidelity.title', [], $card->language) }}</h1>
    <div>{{ $card->customer->display_name ?: $card->customer->firstname }}</div>
    <img class="qr" src="{{ $qr }}" alt="">
    <div><a href="{{ $url }}">{{ __('fidelity.view_card', [], $card->language) }}</a></div>
</body>
</html>
