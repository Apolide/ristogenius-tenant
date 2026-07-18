<?php

namespace Tests\Feature\Layout;

use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('operator', 'web');
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
    }

    public function test_admin_sees_every_active_sidebar_link(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $html = (string) $this->actingAs($admin)->view('layouts.sidebar.base');

        foreach ([
            '/manage/bookings', '/manage/personnel', '/manage/personnel/permissions',
            '/manage/users/calendar', '/admin/calendar', '/manage/customers',
            '/manage/contacts', '/leads', '/manage/leads', '/manage/users',
            route('settings.index'), route('profile.edit'),
            route('settings.message-channel-cases'), route('settings.messages'),
            route('settings.departments'), route('settings.rooms'),
            route('settings.room-tables'), '/settings/tracking',
        ] as $link) {
            $this->assertStringContainsString('href="'.$link.'"', $html);
        }
    }

    public function test_operator_only_sees_links_allowed_by_personnel_permissions(): void
    {
        $operator = User::factory()->create();
        $operator->assignRole('operator');
        $permissions = app(PersonnelPermissionsService::class);
        $permissions->set($operator, PersonnelPermissionsService::CUSTOMERS, true);
        $permissions->set($operator, PersonnelPermissionsService::BOOKINGS, true);

        $html = (string) $this->actingAs($operator)->view('layouts.sidebar.base');

        $this->assertStringContainsString('href="'.route('bookings.index').'"', $html);
        $this->assertStringContainsString('href="'.route('customers.index').'"', $html);
        foreach (['/manage/personnel', '/manage/contacts', route('settings.index')] as $forbiddenLink) {
            $this->assertStringNotContainsString('href="'.$forbiddenLink.'"', $html);
        }
    }
}
