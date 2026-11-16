<?php

namespace Tests\Feature;

use App\Livewire\Header\Notifications;
use App\Models\Notification;
use App\Models\Order;
use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use App\Services\PublicOrders\OrderNotifications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HeaderNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_incoming_order_reaches_role_permissions_without_email(): void
    {
        app(Permissions::class)->ensurePermissionsExist();
        $role = Role::findOrCreate('orders-staff', 'web');
        $role->givePermissionTo(Permissions::CUSTOMER_ORDERS);
        $recipient = User::factory()->create(['enabled' => true, 'email' => '']);
        $recipient->assignRole($role);
        $other = User::factory()->create(['enabled' => true]);
        $disabled = User::factory()->create(['enabled' => false]);
        $disabled->assignRole($role);
        $order = Order::create(['service_date' => today(), 'reference' => 'OC-TEST', 'source' => 'customer', 'fulfillment' => 'takeaway', 'customer_name' => 'Mario']);

        app(OrderNotifications::class)->incoming($order);

        $this->assertSame('customer_order.received', $recipient->notifications()->sole()->type);
        $this->assertSame(0, $other->notifications()->count());
        $this->assertSame(0, $disabled->notifications()->count());
    }

    public function test_read_state_is_private_to_recipient_and_bell_tracks_it(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $notification = Notification::create(['title' => 'Ordine ricevuto', 'message' => 'Da accettare', 'url' => '/manage/customers-orders', 'type' => 'customer_order.received']);
        $notification->users()->attach([$user->id, $other->id]);
        $private = Notification::create(['title' => 'Privata', 'message' => 'Altro utente', 'url' => '/', 'type' => 'general']);
        $private->users()->attach($other->id);

        Livewire::actingAs($user)->test(Notifications::class)
            ->assertSee('Ordine ricevuto')->assertDontSee('Privata')->assertSeeHtml('pulse-success')
            ->call('markAllRead')->assertDontSeeHtml('pulse-success');
        $this->assertSame(1, $other->unreadNotifications()->whereKey($notification->id)->count());
        Livewire::actingAs($user)->test(Notifications::class)->call('open', $private->id)->assertNotFound();
        Livewire::actingAs($other)->test(Notifications::class)->call('open', $notification->id)
            ->assertRedirect('/manage/customers-orders');
        $this->assertSame(0, $other->unreadNotifications()->whereKey($notification->id)->count());
    }
}
