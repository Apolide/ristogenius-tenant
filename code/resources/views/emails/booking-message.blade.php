<!DOCTYPE html><html lang="{{ $delivery['language'] ?? 'it' }}"><body style="margin:0;background:#f4f6f8;font-family:Arial,sans-serif;color:#222">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:24px">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#fff;border-radius:8px"><tr><td style="padding:32px">
    <div style="text-align:center;margin-bottom:24px">
        @if($tenant['logo_url'] ?? null)<img src="{{ $tenant['logo_url'] }}" alt="{{ $tenant['name'] ?? '' }}" style="max-height:80px;max-width:240px">@else<h2>{{ $tenant['name'] ?? config('app.name') }}</h2>@endif
    </div>
    <p>{{ $delivery['recipient']['name'] ?? '' }},</p>
    <p style="line-height:1.6">{!! nl2br(e($delivery['content']['message'] ?? '')) !!}</p>
    @if($delivery['content']['additional_note'] ?? '')<p style="line-height:1.6">{!! nl2br(e($delivery['content']['additional_note'])) !!}</p>@endif
    <table role="presentation" width="100%" style="margin:24px 0;border-collapse:collapse">
        <tr><td style="padding:8px;border:1px solid #ddd"><b>{{ __('bookings.show.datetime', locale: $delivery['language'] ?? 'it') }}</b></td><td style="padding:8px;border:1px solid #ddd">{{ $booking['date'] ?? '' }} {{ $booking['time'] ?? '' }}</td></tr>
        <tr><td style="padding:8px;border:1px solid #ddd"><b>{{ __('bookings.show.people', locale: $delivery['language'] ?? 'it') }}</b></td><td style="padding:8px;border:1px solid #ddd">{{ $booking['pax'] ?? '' }}</td></tr>
    </table>
    <div style="text-align:center;margin:28px 0">@foreach($delivery['actions'] ?? [] as $action)<a href="{{ $action['url'] }}" style="display:inline-block;margin:5px;padding:12px 18px;background:#f59e0b;color:#fff;text-decoration:none;border-radius:5px;font-weight:bold">{{ $action['label'] }}</a>@endforeach</div>
    @if($delivery['actions'] ?? [])<p style="font-size:13px;color:#666">{{ match($delivery['language'] ?? 'it') {'en'=>'If a button does not work, use the corresponding link below:','de'=>'Falls eine Schaltfläche nicht funktioniert, verwenden Sie den entsprechenden Link: ',default=>'Se il bottone non funziona clicca sul link di seguito:'} }}</p>@foreach($delivery['actions'] as $action)<p style="font-size:12px;word-break:break-all"><b>{{ $action['label'] }}:</b> <a href="{{ $action['url'] }}">{{ $action['url'] }}</a></p>@endforeach @endif
</td></tr></table></td></tr></table></body></html>
