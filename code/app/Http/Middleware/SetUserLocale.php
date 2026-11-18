<?php

namespace App\Http\Middleware;

use App\Services\CustomerLanguageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(app(CustomerLanguageService::class)->backend());
        $fallback = app(CustomerLanguageService::class)->backendDefault();
        $locale = strtolower((string) ($request->user()?->lang ?: $fallback));

        app()->setLocale(in_array($locale, $supported, true) ? $locale : $fallback);

        return $next($request);
    }
}
