<?php

namespace App\Services\Marketing;

use App\Models\MessageOutbox;

class MarketingMailer
{
    public function enqueue(string $key, string $email, string $language, string $subject, string $message, string $url, array $context = [], ?\DateTimeInterface $availableAt = null): MessageOutbox
    {
        return MessageOutbox::firstOrCreate(['deduplication_key' => $key], ['event_type' => $key, 'aggregate_type' => 'marketing_confirmation', 'aggregate_id' => explode(':', $key, 2)[1] ?? $key, 'status' => 'pending', 'attempts' => 0, 'available_at' => $availableAt ?? now(),
            'payload' => ['email' => $email, 'language' => $language, 'subject' => $subject, 'message' => $message, 'url' => $url] + $context]);
    }

    public function send(array $data): ?string
    {
        $mail = (new \App\Mail\MarketingMail($data))->locale($data['language']);
        $sent = \Illuminate\Support\Facades\Mail::to($data['email'])->send($mail);

        return $sent?->getMessageId();
    }
}
