<?php

namespace App\Http\Middleware;

use App\Services\CustomerLanguageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCustomerBookingLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = strtolower((string) $request->route('language'));
        abort_unless(array_key_exists($language, app(CustomerLanguageService::class)->enabled()), 404);
        app()->setLocale($language);

        return $next($request);
    }
}
