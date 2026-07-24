<?php

namespace App\Services\Messaging\Channels;

use App\Contracts\Messaging\MessageChannel;
use App\Mail\BookingMessageMail;
use App\Models\MessageOutbox;
use Illuminate\Support\Facades\Mail;

class EmailMessageChannel implements MessageChannel
{
    public function send(MessageOutbox $outbox): bool
    {
        $sent = false;

        foreach ($outbox->payload['deliveries'] ?? [] as $delivery) {
            $email = $delivery['recipient']['email'] ?? null;
            if (! $email) {
                continue;
            }
            $mail = new BookingMessageMail(
                delivery: $delivery,
                tenant: $outbox->payload['tenant'] ?? [],
                booking: $outbox->payload['booking'] ?? [],
            );
            $mail->locale($delivery['language'] ?? 'it');
            Mail::to($email)->send($mail);
            $sent = true;
        }

        return $sent;
    }
}
