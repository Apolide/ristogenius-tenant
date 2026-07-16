<?php

namespace App\Http\Middleware;

use App\Services\Personnel\PersonnelPermissionsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonnelPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission,
    ): Response {
        $user = $request->user();

        if ($user && app(PersonnelPermissionsService::class)->can($user, $permission)) {
            return $next($request);
        }

        Log::warning('Tentativo di accesso a una funzionalità non autorizzata.', [
            'user_id' => $user?->id,
            'permission' => $permission,
            'route' => $request->route()?->getName(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        abort(403, 'Non sei autorizzato ad accedere a questa funzionalità.');
    }
}
