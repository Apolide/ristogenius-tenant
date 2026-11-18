<?php

namespace Tests\Feature\Personnel;

use App\Livewire\Personnel\AccountProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_sees_own_profile_and_can_only_update_name_and_language(): void
    {
        $this->withoutVite();
        config(['tenant.backend_languages' => 'it,en,gr']);
        $user = User::factory()->create([
            'lang' => 'it', 'enabled' => true, 'phone' => '+393401234567',
            'receive_whatsapp_notifications' => false, 'receive_telegram_notifications' => false,
        ]);
        $user->assignRole(Role::findOrCreate('operator', 'web'));
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('account.profile'))->assertOk()
            ->assertSee('Il mio profilo')->assertSee($user->email)
            ->assertSee(route('notifications.index'))->assertDontSee($other->email);
        $this->get(route('personnel.edit', $other))->assertForbidden();

        Livewire::test(AccountProfile::class)
            ->set('name', 'Nome aggiornato')->set('lang', 'gr')
            ->set('email', 'forged@example.test')->set('phone', '+390000000000')
            ->set('role', 'admin')->set('enabled', false)
            ->set('receive_whatsapp_notifications', true)->set('receive_telegram_notifications', true)
            ->call('submit')->assertHasNoErrors()->assertRedirect(route('account.profile'));

        $updated = $user->fresh();
        $this->assertSame('Nome aggiornato', $updated->name);
        $this->assertSame('gr', $updated->lang);
        $this->assertSame($user->email, $updated->email);
        $this->assertSame($user->phone, $updated->phone);
        $this->assertTrue($updated->hasRole('operator'));
        $this->assertTrue($updated->enabled);
        $this->assertFalse($updated->receive_whatsapp_notifications);
        $this->assertFalse($updated->receive_telegram_notifications);

        $this->putJson('/user/profile-information', ['name' => 'Altro nome', 'email' => 'forged@example.test'])
            ->assertOk();
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_admin_can_edit_own_contact_details_and_notification_preferences(): void
    {
        $this->withoutVite();
        $admin = User::factory()->create(['lang' => 'it']);
        $admin->assignRole(Role::findOrCreate('admin', 'web'));
        $this->actingAs($admin)->get(route('account.profile'))->assertOk();

        Livewire::test(AccountProfile::class)
            ->set('name', 'Admin aggiornato')->set('lang', 'en')
            ->set('email', 'updated@example.test')->set('phone', '+39 340 123 4567')
            ->set('receive_telegram_notifications', true)
            ->call('submit')->assertHasNoErrors()->assertRedirect(route('account.profile'));

        $updated = $admin->fresh();
        $this->assertSame('updated@example.test', $updated->email);
        $this->assertSame('+393401234567', $updated->phone);
        $this->assertTrue($updated->receive_telegram_notifications);
        $this->assertTrue($updated->hasRole('admin'));
    }
}
