<?php

namespace Tests\Feature\Personnel;

use App\Http\Middleware\EnsurePersonnelPermission;
use App\Livewire\Personnel\PersonnelPermissions;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Database\Seeders\PersonnelPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PersonnelPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('operator', 'web');
        app(PersonnelPermissionsService::class)->ensurePermissionsExist();
    }

    public function test_admin_can_assign_and_revoke_a_permission_from_the_matrix(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $employee = User::factory()->create();
        $employee->assignRole('operator');

        $this->actingAs($admin);

        Livewire::test(PersonnelPermissions::class)
            ->set('assigned.'.$employee->id.'.'.PersonnelPermissionsService::BOOKINGS, true)
            ->call('toggle', $employee->id, PersonnelPermissionsService::BOOKINGS)
            ->assertHasNoErrors();

        $this->assertTrue($employee->fresh()->hasDirectPermission(PersonnelPermissionsService::BOOKINGS));

        Livewire::test(PersonnelPermissions::class)
            ->set('assigned.'.$employee->id.'.'.PersonnelPermissionsService::BOOKINGS, false)
            ->call('toggle', $employee->id, PersonnelPermissionsService::BOOKINGS)
            ->assertHasNoErrors();

        $this->assertFalse($employee->fresh()->hasDirectPermission(PersonnelPermissionsService::BOOKINGS));
    }

    public function test_non_admin_cannot_open_the_permission_matrix(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('operator');

        $this->actingAs($employee)
            ->get(route('personnel.permissions'))
            ->assertForbidden();
    }

    public function test_permission_middleware_allows_assigned_employee_and_admin(): void
    {
        $service = app(PersonnelPermissionsService::class);
        $employee = User::factory()->create();
        $employee->assignRole('operator');
        $service->set($employee, PersonnelPermissionsService::CUSTOMERS, true);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $middleware = app(EnsurePersonnelPermission::class);

        foreach ([$employee, $admin] as $user) {
            $request = Request::create('/manage/customers');
            $request->setUserResolver(fn () => $user);

            $response = $middleware->handle(
                $request,
                fn () => new Response('ok'),
                PersonnelPermissionsService::CUSTOMERS,
            );

            $this->assertSame(200, $response->getStatusCode());
        }
    }

    public function test_permission_middleware_blocks_and_logs_unauthorized_access(): void
    {
        Log::spy();

        $employee = User::factory()->create();
        $employee->assignRole('operator');
        $request = Request::create('/manage/customers');
        $request->setUserResolver(fn () => $employee);

        try {
            app(EnsurePersonnelPermission::class)->handle(
                $request,
                fn () => new Response('ok'),
                PersonnelPermissionsService::CUSTOMERS,
            );
            $this->fail('The middleware did not block the request.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_demo_seeder_grants_every_feature_to_non_admin_personnel(): void
    {
        $employee = User::factory()->create();
        $employee->assignRole('operator');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->seed(PersonnelPermissionsSeeder::class);

        $this->assertCount(
            count(PersonnelPermissionsService::FEATURES),
            $employee->fresh()->permissions,
        );
        $this->assertCount(0, $admin->fresh()->permissions);
    }
}
