<?php

namespace App\Services;

use App\Mail\WaitlistTableReadyMail;
use App\Models\BookingWaitlistEntry;
use App\Services\Settings\TenantSettingsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WaitlistNotificationService
{
    public function send(BookingWaitlistEntry $entry): array
    {
        $entry->loadMissing('customer');
        $configured = app(TenantSettingsService::class)->messageChannelCaseChannels('waitlist_table_ready');
        $sent = [];

        if (in_array('email', $configured, true) && $entry->customer->email) {
            Mail::to($entry->customer->email)->send(new WaitlistTableReadyMail($entry));
            $sent[] = 'email';
        }

        foreach (['whatsapp', 'sms'] as $channel) {
            if (in_array($channel, $configured, true) && $entry->customer->phone) {
                Log::notice('Canale lista di attesa configurato ma provider non disponibile', [
                    'waitlist_entry_id' => $entry->id,
                    'channel' => $channel,
                ]);
            }
        }

        return $sent;
    }
}
