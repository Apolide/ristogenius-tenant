<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('messages:publish-outbox')
    ->everyMinute()
    ->withoutOverlapping();

// Schedule::job(new \App\Jobs\Payments\GenerateTenantsMonthlyCostJob)->monthlyOn(1, '05:00');
// Schedule::job(new \App\Jobs\Payments\GetTenantsMonthlyToChargeJob)->monthlyOn(1, '05:15');

//Schedule::job(new \App\Jobs\Payments\GetToRetryTenantsMonthlyToChargeJob)->everySixHours();

// Schedule::job(new \App\Jobs\Funds\CheckLowFundsJob(15.00))
//     ->everySixHours()
//     ->name('check-low-funds')
//     ->withoutOverlapping();

// // Acquisti automatici ogni 2 ore (soglia 10€ per acquisti automatici di 50€)
// Schedule::job(new \App\Jobs\Funds\CheckAndAutoPurchaseFundsJob())
//     ->everyTwoHours()
//     ->name('auto-purchase-funds')
//     ->withoutOverlapping();

Schedule::command('customer-orders:prune-access')->dailyAt('03:45')->withoutOverlapping();

Schedule::command('fiscal:sync-invoices')->everyFiveMinutes()->withoutOverlapping();

Schedule::command('fiscal:queue-copies')->everyMinute()->withoutOverlapping();

Artisan::command('marketing:dispatch-due', function () {
    $this->info((string) app(\App\Services\Marketing\CampaignService::class)->dispatchDue());
})->purpose('Queue due marketing campaign deliveries');
Schedule::command('marketing:dispatch-due')->everyMinute()->withoutOverlapping();

Artisan::command('polls:dispatch-due', function () {
    app(\App\Services\Polls\PollDelivery::class)->dispatchDue();
})->purpose('Queue due post-visit poll invitations');
Schedule::command('polls:dispatch-due')->everyMinute()->withoutOverlapping();
