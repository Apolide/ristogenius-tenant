<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

use App\Http\Middleware\CheckSystemToken;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
        // $middleware->appendToGroup('api', \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
        // // ✅ quando una rotta usa guest:* e l'utente è già loggato
        // $middleware->redirectUsersTo(function (Request $request) {
        //     if ($request->is('customer') || $request->is('customer/*')) {
        //         return '/customer/profile';
        //     }
        //     return '/login';
        // });

        // $middleware->validateCsrfTokens(except: [
        //     // 'telegram/operator/bind/callback',
        //     // 'telegram/customer/bind/callback',
        //     // '/stripe/webhook',
        //     // '/stripe/connect/webhook',
        //     // '/stripe/connect/test/webhook',
        //     // '/connect/takeaway/create-checkout-session',
        //     // '/connect/takeaway/refund',
        //     // '/meta/webhook'
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // $exceptions->render(function (InvalidSignatureException $e, Request $request) {
        //     if ($request->is('customer/*')) {
        //         return redirect('/customer/login')
        //             ->with('error', 'Link non valido o scaduto. Richiedine uno nuovo.');
        //     }
        //     return null;
        // });
    })->create();
