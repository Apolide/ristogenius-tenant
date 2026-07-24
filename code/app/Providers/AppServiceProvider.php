<?php

namespace App\Providers;

use App\Contracts\Messaging\MessageTransport;
use App\Services\Messaging\Transports\RedisQueueMessageTransport;
use App\Services\Notification\NotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MessageTransport::class, function ($app) {
            return match (config('messaging.transport')) {
                'redis' => $app->make(RedisQueueMessageTransport::class),
                default => throw new \RuntimeException('Unsupported message transport ['.config('messaging.transport').'].')
            };
        });

        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        //  RateLimiter::for('customer-login-email', function (Request $request) {

        //     // ✅ In non-production niente rate limit (evita 429 in locale)
        //     if (! app()->environment('production')) {
        //         return Limit::none();
        //     }

        //     $email = strtolower((string) $request->input('email'));

        //     if ($email === '') {
        //         return Limit::perMinute(5)->by($request->ip());
        //     }

        //     return Limit::perMinutes(10, 3)->by('login-email:' . $email);
        // });

        // RateLimiter::for('customer-login-ip', function (Request $request) {

        //     // ✅ In non-production niente rate limit
        //     if (! app()->environment('production')) {
        //         return Limit::none();
        //     }

        //     return Limit::perMinutes(10, 20)->by('login-ip:' . $request->ip());
        // });

    }
}
