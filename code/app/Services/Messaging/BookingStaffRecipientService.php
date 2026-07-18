<?php

namespace App\Services\Messaging;

use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Support\Collection;

class BookingStaffRecipientService
{
    /** @return Collection<int, User> */
    public function emailRecipients(): Collection
    {
        return User::query()
            ->where('enabled', true)
            ->whereNotNull('email')
            ->where(function ($query): void {
                $query->whereHas('roles', fn ($roles) => $roles->where('name', 'admin'))
                    ->orWhereHas('permissions', fn ($permissions) => $permissions->where('name', PersonnelPermissionsService::BOOKINGS));
            })
            ->get();
    }
}
