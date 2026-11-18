<!doctype html><html lang="{{ $content['language'] }}"><head><meta charset="utf-8"><title>{{ $content['subject'] }}</title></head><body style="font-family:Arial,sans-serif;color:#222;background:#f6f6f6;padding:20px"><div style="max-width:640px;margin:auto;background:#fff;padding:24px">
@if(!empty($content['preheader']))<div style="display:none;max-height:0;overflow:hidden">{{ $content['preheader'] }}</div>@endif
@if(!empty($content['branding']))
    @if(!empty($content['branding']['logo_url']))<img src="{{ $content['branding']['logo_url'] }}" alt="{{ $content['branding']['name'] }}" style="max-width:180px;max-height:70px;height:auto">@endif
    <p style="color:#64748b">{{ $content['branding']['name'] }}</p>
@endif
<h1 style="font-size:24px">{{ $content['heading'] ?? $content['subject'] }}</h1>
@if(!empty($content['assignment_id'])) @php($assignment=\App\Models\CouponAssignment::find($content['assignment_id'])) @if($assignment && $url=$assignment->bookingUrl($content['language']))<p><a href="{{ $url }}" style="background:#2563eb;color:white;padding:12px 20px;display:inline-block">{{ __('marketing.book_coupon') }}</a></p>@endif @endif
@if(!empty($content['image'])) @if(!empty($content['url']))<a href="{{ $content['url'] }}">@endif<img src="{{ $content['image'] }}" alt="{{ $content['subject'] }}" style="max-width:100%;height:auto">@if(!empty($content['url']))</a>@endif @endif
@if(!empty($content['html']))<div>{!! $content['html'] !!}</div>@endif
@if(!empty($content['message']))
    <p style="line-height:1.6">
        @if(!empty($content['linkify_message']))
            @foreach(preg_split('~(https?://[^\s<>"\\x27]+)~iu', $content['message'], -1, PREG_SPLIT_DELIM_CAPTURE) as $index => $part)
                @if($index % 2 === 1)
                    @php($link = rtrim($part, '.,;!?)'))
                    <a href="{{ $link }}">{{ $link }}</a>{{ substr($part, strlen($link)) }}
                @else
                    {!! nl2br(e($part)) !!}
                @endif
            @endforeach
        @else
            {!! nl2br(e($content['message'])) !!}
        @endif
    </p>
@endif
@if(!empty($content['coupon_title']))
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px;margin:20px 0">
        <h2 style="font-size:20px;margin:0 0 12px">{{ $content['coupon_title'] }}</h2>
        @foreach($content['coupon_summary'] as $label => $value)<p style="margin:8px 0"><strong>{{ $label }}:</strong> {{ $value }}</p>@endforeach
    </div>
@endif
@if(isset($assignment) && $assignment)<h2>{{ $assignment->snapshot['translations'][$content['language']]['title'] }}</h2><div>{!! $assignment->snapshot['translations'][$content['language']]['description'] !!}</div><div>{!! $assignment->snapshot['translations'][$content['language']]['terms'] !!}</div><p>{{ __('marketing.valid_until') }}: {{ substr($assignment->snapshot['valid_until'] ?? '',0,10) ?: '—' }}</p><p><a href="{{ $assignment->url($content['language']) }}">{{ __('marketing.view_coupon') }}</a> · <a href="{{ route('coupons.pdf',['language'=>$content['language'],'token'=>$assignment->access_token]) }}">{{ __('marketing.download') }}</a></p>@endif
@if(!empty($content['url']))<p><a href="{{ $content['url'] }}" style="background:#2563eb;color:white;padding:12px 20px;display:inline-block">{{ $content['button'] ?? __('marketing.open') }}</a></p>@endif
@if(!empty($content['notice']))<p style="font-size:13px;color:#64748b;line-height:1.5">{{ $content['notice'] }}</p>@endif
@if(!empty($content['branding']))<hr style="border:0;border-top:1px solid #e2e8f0"><p style="font-size:13px;color:#64748b">{{ $content['branding']['name'] }}@if(!empty($content['branding']['address'])) · {{ $content['branding']['address'] }}@endif</p>@endif
@if(!empty($content['unsubscribe']))<hr><p><a href="{{ $content['unsubscribe'] }}">{{ __('marketing.unsubscribe') }}</a></p>@endif
</div></body></html>
