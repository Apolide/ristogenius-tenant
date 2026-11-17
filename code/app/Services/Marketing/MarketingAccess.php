<?php

namespace App\Services\Marketing;

use App\Services\Personnel\PersonnelPermissionsService as Permissions;

class MarketingAccess
{
    public static function authorize(): void
    {
        abort_unless(auth()->user() && app(Permissions::class)->can(auth()->user(), Permissions::MARKETING), 403);
    }
}
