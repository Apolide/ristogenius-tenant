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

    public function test_history_paginates_only_the_users_notifications_and_opens_the_operation(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['lang' => 'it']);
        $other = User::factory()->create();
        $user->assignRole(Role::findOrCreate('admin', 'web'));
        for ($i = 1; $i <= 31; $i++) {
            $notification = Notification::create([
                'title' => "Avviso $i.", 'message' => "Messaggio $i.",
                'url' => '/manage/customers-orders', 'type' => 'general',
            ]);
            $notification->users()->attach($user->id);
            if ($i === 1) {
                $oldest = $notification;
                $notification->users()->attach($other->id);
            }
        }

        $private = Notification::create(['title' => 'Avviso di un altro utente', 'message' => 'Privata', 'url' => '/', 'type' => 'general']);
        $private->users()->attach($other->id);

        $this->actingAs($user)->get(route('notifications.index'))->assertOk()
            ->assertSee('Avviso 31.')->assertSee('Vedi tutte')->assertDontSee('Avviso di un altro utente');
        $this->get(route('notifications.index', ['page' => 3]))->assertOk()
            ->assertSee('Avviso 1.')->assertSee(route('notifications.show', $oldest->id));
        $this->get(route('notifications.show', $private->id))->assertNotFound();
        $this->get(route('notifications.show', $oldest->id))->assertRedirect('/manage/customers-orders');
        $this->assertFalse($user->unreadNotifications()->whereKey($oldest->id)->exists());
        $this->assertTrue($other->unreadNotifications()->whereKey($oldest->id)->exists());
    }

    public function test_history_filters_translated_content_type_period_and_read_state(): void
    {
        $this->withoutVite();
        config(['tenant.backend_languages' => 'it,en']);
        $user = User::factory()->create(['lang' => 'en']);
        $other = User::factory()->create();
        $shared = [
            'title' => 'Nuovo ordine :reference',
            'message' => 'Consegna · :customer',
            'type' => 'customer_order.received',
            'url' => '/manage/customers-orders',
            'translation_data' => [
                'title' => 'public_orders.notifications.new_order_reference',
                'message' => 'public_orders.notifications.delivery_customer',
                'parameters' => ['reference' => 'REF-123', 'customer' => 'Mario'],
            ],
        ];
        $matching = Notification::create($shared);
        $matching->users()->attach($user);
        $read = Notification::create($shared);
        $read->users()->attach($user, ['read_at' => now()]);
        $older = Notification::create($shared);
        $older->forceFill(['created_at' => now()->subDays(2)])->save();
        $older->users()->attach($user);
        $private = Notification::create(['title' => 'Privata', 'message' => 'Privata', 'type' => 'private_type', 'url' => '/']);
        $private->users()->attach($other);

        $this->actingAs($user)->get(route('notifications.index', [
            'q' => 'DELIVERY · MARIO',
            'type' => 'customer_order.received',
            'period' => 'custom',
            'from' => today()->toDateString(),
            'to' => today()->toDateString(),
            'status' => 'unread',
        ]))->assertOk()
            ->assertViewHas('notifications', fn ($page) => $page->getCollection()->pluck('id')->all() === [$matching->id])
            ->assertViewHas('typeLabels', ['customer_order.received' => 'New customer order'])
            ->assertSee('la-shopping-cart')->assertSee('Apply filters');

        $this->get(route('notifications.index', ['period' => 'week', 'status' => 'read']))
            ->assertOk()->assertViewHas('notifications', fn ($page) => $page->getCollection()->pluck('id')->all() === [$read->id]);
    }
}
