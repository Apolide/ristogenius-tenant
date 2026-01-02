<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class UpdateCustomerLastActionOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        // Solo per guard customer
        if ($event->guard !== 'customer') {
            return;
        }

        $user = $event->user;

        // Extra safety: se non è un Customer, esci
        if (!($user instanceof \App\Models\Customer)) {
            return;
        }

        DB::table('customers')
            ->where('id', $user->id)
            ->update(['last_action_at' => now()]);
    }
}
