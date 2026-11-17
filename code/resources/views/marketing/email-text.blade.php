{{ $content['subject'] }}

{{ $content['message'] ?? '' }}
{{ html_entity_decode(strip_tags($content['html'] ?? ''), ENT_QUOTES, 'UTF-8') }}

@if(!empty($content['assignment_id']))
@php($assignment=\App\Models\CouponAssignment::find($content['assignment_id']))
@if($assignment)
{{ __('marketing.book_coupon') }}: {{ $assignment->bookingUrl($content['language']) ?? $assignment->url($content['language']) }}
{{ html_entity_decode(strip_tags($assignment->snapshot['translations'][$content['language']]['description']),ENT_QUOTES,'UTF-8') }}
{{ html_entity_decode(strip_tags($assignment->snapshot['translations'][$content['language']]['terms']),ENT_QUOTES,'UTF-8') }}
{{ __('marketing.view_coupon') }}: {{ $assignment->url($content['language']) }}
@endif
@endif
@if(!empty($content['coupon_title']))
{{ $content['coupon_title'] }}
@foreach($content['coupon_summary'] as $label => $value)
{{ $label }}: {{ $value }}
@endforeach
@endif
{{ $content['button'] ?? '' }}
{{ $content['url'] ?? '' }}
{{ $content['notice'] ?? '' }}
@if(!empty($content['branding']))
{{ $content['branding']['name'] }}
{{ $content['branding']['address'] ?? '' }}
@endif
@if(!empty($content['unsubscribe']))
{{ __('marketing.unsubscribe') }}: {{ $content['unsubscribe'] }}
@endif
