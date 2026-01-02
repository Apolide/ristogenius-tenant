<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Log;

class SubscriberService
{
    /**
     * Unsubscribe a subscriber by token.
     *
     * @param string $token
     * @return bool True if unsubscribed, false otherwise.
     */
    public function unsubscribe(string $token): bool
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();
        if (! $subscriber) {
            Log::warning("Unsubscribe attempted with invalid token: {$token}");
            return false;
        }
        if ($subscriber->unsubscribed) {
            // Already unsubscribed
            return true;
        }
        $subscriber->unsubscribed = true;
        $subscriber->save();
        return true;
    }
}