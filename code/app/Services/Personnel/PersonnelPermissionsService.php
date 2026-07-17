<?php

namespace App\Services\Personnel;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PersonnelPermissionsService
{
    public const AI = 'personnel-manage-ai';

    public const CALENDAR = 'personnel-manage-calendar';

    public const CASH_REGISTER = 'personnel-manage-cash-register';

    public const CUSTOMERS = 'personnel-manage-customers';

    public const ORDERS = 'personnel-manage-orders';

    public const MARKETING = 'personnel-manage-marketing';

    public const MARKETPLACE = 'personnel-manage-marketplace';

    public const BOOKINGS = 'personnel-manage-bookings';

    public const PRODUCTS = 'personnel-manage-products';

    public const ROOMS = 'personnel-manage-rooms';

    public const SHIFTS = 'personnel-manage-shifts';

    /** @var array<string, string> */
    public const FEATURES = [
        self::AI => 'ai',
        self::CALENDAR => 'calendar',
        self::CASH_REGISTER => 'cash_register',
        self::CUSTOMERS => 'customers',
        self::ORDERS => 'orders',
        self::MARKETING => 'marketing',
        self::MARKETPLACE => 'marketplace',
        self::BOOKINGS => 'bookings',
        self::PRODUCTS => 'products',
        self::ROOMS => 'rooms',
        self::SHIFTS => 'shifts',
    ];

    /** @return array<string, string> */
    public function features(): array
    {
        return collect(self::FEATURES)
            ->map(fn (string $key): string => __("personnel.features.{$key}"))
            ->all();
    }

    /** @return Collection<int, Permission> */
    public function ensurePermissionsExist(): Collection
    {
        return collect(array_keys(self::FEATURES))
            ->map(fn (string $permission): Permission => Permission::findOrCreate($permission, 'web'));
    }

    public function can(User $user, string $feature): bool
    {
        return $user->hasRole('admin') || $user->can($feature);
    }

    public function set(User $user, string $feature, bool $enabled): void
    {
        abort_unless(array_key_exists($feature, self::FEATURES), 404);

        $permission = Permission::findOrCreate($feature, 'web');

        $enabled
            ? $user->givePermissionTo($permission)
            : $user->revokePermissionTo($permission);
    }
}
