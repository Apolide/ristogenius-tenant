<p>{{ $booking->customer->firstname }},</p>
<p>{!! nl2br(e($messageContent['message'])) !!}</p>
@if($messageContent['additional_note'] ?? '')
    <p>{!! nl2br(e($messageContent['additional_note'])) !!}</p>
@endif
