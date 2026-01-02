<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSystemToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $systemToken = config('app.system_token');

        $authHeader = $request->header('Authorization');

        // Controlla che il formato sia "Bearer <token>"
        if (!$authHeader || !preg_match('/^Bearer\s+(.+)$/', $authHeader, $matches)) {
            return response()->json(['message' => 'Token mancante o non valido'], 401);
        }

        $receivedToken = $matches[1];

        if ($receivedToken !== $systemToken) {
            return response()->json(['message' => 'Token non valido'], 403);
        }

        return $next($request);
    }
}
