<!doctype html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} · {{ $branding['name'] }}</title>
    <style>
        @page { margin: 13pt 16pt; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7pt; color: #243247; margin: 0; }
        .brand { text-align: center; border-bottom: 1pt solid #dbe3ed; padding-bottom: 5pt; }
        .brand table { width: 100%; border-collapse: collapse; }
        .brand td { vertical-align: middle; }
        .brand .logo-cell { width: 25%; text-align: left; }
        .logo { max-width: 50pt; max-height: 22pt; }
        .name { font-size: 9pt; font-weight: bold; }
        .address { font-size: 6pt; color: #64748b; margin-top: 2pt; }
        h1 { font-size: 11pt; line-height: 1.15; margin: 7pt 0 5pt; color: #0f172a; text-align: center; }
        .summary { width: 100%; border-collapse: collapse; }
        .summary td { padding: 2pt 0; border-bottom: .5pt solid #e2e8f0; vertical-align: top; }
        .summary td:first-child { font-weight: bold; width: 43%; padding-right: 5pt; }
        .qr { text-align: center; margin-top: 5pt; page-break-inside: avoid; }
        .qr img { width: 90pt; height: 90pt; }
        .instructions { font-size: 6.5pt; line-height: 1.25; margin: 3pt 0; }
        .notice { font-size: 6pt; color: #64748b; line-height: 1.25; margin: 3pt 0; }
        .code { font-size: 5pt; color: #64748b; text-align: center; margin-top: 3pt; }
        .revoked { color: #b91c1c; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
    <div class="brand">
        <table><tr>
            @if($logo)<td class="logo-cell"><img class="logo" src="{{ $logo }}" alt="{{ $branding['name'] }}"></td>@endif
            <td><div class="name">{{ $branding['name'] }}</div>
            @if($branding['address'])<div class="address">{{ $branding['address'] }}</div>@endif</td>
        </tr></table>
    </div>
    <h1>{{ $title }}</h1>
    @if($assignment->revoked_at)<p class="revoked">{{ __('marketing.revoked', [], $language) }}</p>@endif
    <table class="summary">@foreach($summary as $label => $value)<tr><td>{{ $label }}</td><td>{{ $value }}</td></tr>@endforeach</table>
    <div class="qr">
        <a href="{{ $assignment->url($language) }}"><img src="{{ $qr }}" alt="QR"></a>
        <p class="instructions">{{ __('marketing.pdf_instructions', [], $language) }}</p>
        <p class="notice">{{ __('marketing.personal_notice', [], $language) }}</p>
        <p class="notice">{{ __('marketing.pdf_live_status', [], $language) }}</p>
    </div>
    <div class="code">{{ $assignment->id }}</div>
</body>
</html>
