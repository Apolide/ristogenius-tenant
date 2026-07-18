<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys((array) config('tenant.backend_languages', []));
        $fallback = (string) config('tenant.default_backend_language', 'it');
        $locale = strtolower((string) ($request->user()?->lang ?: $fallback));

        app()->setLocale(in_array($locale, $supported, true) ? $locale : $fallback);

        return $next($request);
    }
}
