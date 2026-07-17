<?php

namespace Tests\Feature\Personnel;

use App\Actions\Fortify\ResetUserPassword;
use App\Livewire\Personnel\PersonnelCreate;
use App\Livewire\Personnel\PersonnelEdit;
use App\Models\User;
use App\Notifications\Mail\User\EmployeeInvitation;
use App\Services\Personnel\PersonnelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PersonnelManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('operator', 'web');
        Role::findOrCreate('manager', 'web');
    }

    public function test_admin_can_create_an_employee_and_send_the_activation_invitation(): void
    {
        Notification::fake();

        Livewire::test(PersonnelCreate::class)
            ->set('name', 'Mario Rossi')
            ->set('email', 'MARIO@EXAMPLE.TEST')
            ->set('phone', '+39 340 123 4567')
            ->set('role', 'operator')
            ->set('lang', 'it')
            ->set('receive_whatsapp_notifications', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('personnel.index'));

        $employee = User::query()->where('email', 'mario@example.test')->firstOrFail();

        $this->assertFalse($employee->enabled);
        $this->assertNull($employee->activated_at);
        $this->assertSame('+393401234567', $employee->phone);
        $this->assertTrue($employee->receive_whatsapp_notifications);
        $this->assertTrue($employee->hasRole('operator'));
        Notification::assertSentTo($employee, EmployeeInvitation::class);
    }

    public function test_setting_the_password_activates_an_invited_employee(): void
    {
        $employee = User::factory()->create([
            'enabled' => false,
            'email_verified_at' => null,
            'invited_at' => now(),
            'activated_at' => null,
        ]);

        app(ResetUserPassword::class)->reset($employee, [
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $employee->refresh();

        $this->assertTrue($employee->enabled);
        $this->assertNotNull($employee->activated_at);
        $this->assertNotNull($employee->email_verified_at);
    }

    public function test_admin_can_edit_personnel_data_role_and_notification_preferences(): void
    {
        $employee = User::factory()->create([
            'name' => 'Mario Rossi',
            'email' => 'mario@example.test',
            'phone' => '+393401111111',
            'lang' => 'it',
            'receive_whatsapp_notifications' => false,
            'receive_telegram_notifications' => false,
        ]);
        $employee->assignRole('operator');

        Livewire::test(PersonnelEdit::class, ['user' => $employee])
            ->assertSet('name', 'Mario Rossi')
            ->assertSet('role', 'operator')
            ->set('name', 'Mario Bianchi')
            ->set('email', 'MARIO.BIANCHI@EXAMPLE.TEST')
            ->set('phone', '+39 340 222 3333')
            ->set('role', 'manager')
            ->set('lang', 'en')
            ->set('receive_whatsapp_notifications', true)
            ->set('receive_telegram_notifications', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('personnel.index'));

        $employee->refresh();

        $this->assertSame('Mario Bianchi', $employee->name);
        $this->assertSame('mario.bianchi@example.test', $employee->email);
        $this->assertSame('+393402223333', $employee->phone);
        $this->assertSame('en', $employee->lang);
        $this->assertTrue($employee->receive_whatsapp_notifications);
        $this->assertTrue($employee->receive_telegram_notifications);
        $this->assertTrue($employee->hasRole('manager'));
        $this->assertFalse($employee->hasRole('operator'));
    }

    public function test_personnel_roles_include_admin_and_languages_come_from_tenant_configuration(): void
    {
        config()->set('tenant.languages', 'it,en,fr');

        $service = app(PersonnelService::class);

        $this->assertContains('admin', $service->roles());
        $this->assertSame([
            'it' => 'IT',
            'en' => 'EN',
            'fr' => 'FR',
        ], $service->languages());
    }

    public function test_personnel_routes_are_separate_from_legacy_user_routes(): void
    {
        $this->assertSame('/manage/personnel', route('personnel.index', absolute: false));
        $this->assertSame('/manage/personnel/create', route('personnel.create', absolute: false));
        $this->assertSame('/manage/personnel/123/edit', route('personnel.edit', 123, absolute: false));
        $this->assertSame('/manage/users', route('users.index', absolute: false));
        $this->assertSame('/manage/users/create', route('users.create', absolute: false));
    }
}
