<?php

namespace Tests\Feature\Orders;

use App\Livewire\CashDesk\Show;
use App\Livewire\Kds\Board;
use App\Livewire\Orders\Editor;
use App\Livewire\Orders\RoomMap;
use App\Models\Department;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\Order;
use App\Models\ProductionTicket;
use App\Models\Room;
use App\Models\RoomTable;
use App\Models\User;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    private RoomTable $table;

    private DigitalMenuCategory $category;

    private DigitalMenuProduct $pizza;

    private DigitalMenuProduct $starter;

    private DigitalMenuIngredient $extra;

    private DigitalMenuIngredient $tomato;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin);
        app(Permissions::class)->ensurePermissionsExist();
        $this->service = app(OrderService::class);
        $room = Room::create(['name' => 'Sala blu', 'active' => true, 'service_charge' => '2.50', 'service_charge_percentage' => '10']);
        $this->table = RoomTable::create(['room_id' => $room->id, 'name' => '19', 'x' => 100, 'y' => 80]);
        $menu = DigitalMenu::create(['name' => ['it' => 'Menu cena'], 'slug' => 'cena', 'enabled_languages' => ['it'], 'is_visible' => true, 'is_published' => false, 'service_charge' => 99]);
        $this->category = $menu->categories()->create(['name' => ['it' => 'Piatti'], 'is_enabled' => true]);
        $pizzeria = Department::create(['name' => 'Pizzeria', 'production' => true]);
        $kitchen = Department::create(['name' => 'Cucina', 'production' => true]);
        $this->pizza = DigitalMenuProduct::create(['name' => ['it' => 'Margherita'], 'price' => '8.00', 'vat' => 10, 'department_id' => $pizzeria->id, 'is_active' => true]);
        $this->starter = DigitalMenuProduct::create(['name' => ['it' => 'Bruschetta'], 'price' => '4.00', 'vat' => 10, 'department_id' => $kitchen->id, 'is_active' => true]);
        $this->category->products()->attach($this->pizza, ['menu_price' => '9.50', 'is_visible' => true]);
        $this->category->products()->attach($this->starter, ['is_visible' => true]);
        $this->tomato = DigitalMenuIngredient::create(['name' => ['it' => 'Pomodoro'], 'remove_price' => '-0.50', 'add_price' => '0.50']);
        $this->extra = DigitalMenuIngredient::create(['name' => ['it' => 'Burrata'], 'add_price' => '2.00']);
        $this->pizza->ingredients()->attach($this->tomato, ['quantity' => 1]);
    }

    private function open(): Order
    {
        return $this->service->open($this->table->id, 2);
    }

    private function add(Order $order, ?DigitalMenuProduct $product = null, array $added = [], array $removed = []): void
    {
        $this->service->saveItem($order->id, $order->courses()->first()->id, $this->category->id, ($product ?? $this->pizza)->id, 1, 'Ben cotta', $added, $removed);
    }

    private function send(Order $order): void
    {
        $this->service->send($order->id, $order->courses()->first()->id);
    }

    public function test_open_is_idempotent_and_uses_only_room_cover_charge(): void
    {
        $order = $this->open();
        $same = $this->open();
        $this->assertSame($order->id, $same->id);
        $this->assertSame(500, $order->totalCents());
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_courses', 1);
    }

    public function test_price_modifiers_and_history_survive_catalog_changes(): void
    {
        $order = $this->open();
        $this->add($order, added: [$this->extra->id], removed: [$this->tomato->id]);
        $this->assertSame(1600, $order->totalCents());
        $this->pizza->update(['price' => '500', 'name' => ['it' => 'Nuovo nome']]);
        $this->extra->delete();
        $this->pizza->delete();
        $this->table->room->update(['service_charge' => 99]);
        $this->assertSame(1600, $order->totalCents());
        $this->assertSame('Margherita', $order->items()->first()->product_name);
        $this->assertDatabaseHas('order_item_modifiers', ['ingredient_name' => 'Burrata']);
    }

    public function test_sending_splits_departments_and_is_idempotent(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->add($order, $this->starter);
        $this->send($order);
        $this->send($order);
        $this->assertDatabaseCount('production_tickets', 2);
        $this->assertSame(2, $order->items()->whereNotNull('production_ticket_id')->count());
        $this->assertSame(1, $order->events()->where('action', 'course_sent')->count());
    }

    public function test_any_department_starting_locks_the_whole_course(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->add($order, $this->starter);
        $this->send($order);
        $ticket = ProductionTicket::first();
        $this->service->advance($ticket->id, 'queued');
        $item = $order->items()->where('production_ticket_id', '!=', $ticket->id)->first();
        $this->expectException(ValidationException::class);
        $this->service->removeItem($order->id, $item->id);
    }

    public function test_queued_items_can_be_removed_without_empty_kds_tickets(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $this->service->removeItem($order->id, $order->items()->first()->id);
        $this->assertDatabaseHas('production_tickets', ['status' => 'cancelled']);
        $this->assertSame(500, $order->totalCents());
    }

    public function test_duplicate_kds_action_does_not_skip_a_state(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $ticket = ProductionTicket::first();
        $this->service->advance($ticket->id, 'queued');
        try {
            $this->service->advance($ticket->id, 'queued');
            $this->fail('Expected stale state rejection');
        } catch (ValidationException) {
        }
        $this->assertSame('preparing', $ticket->fresh()->status);
    }

    public function test_payment_is_idempotent_frees_table_and_preserves_kds_work(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $ticket = ProductionTicket::first();
        $this->service->advance($ticket->id, 'queued');
        $this->service->pay($order->id, 'cash', $order->totalCents());
        $this->service->pay($order->id, 'cash', $order->totalCents());
        $this->assertDatabaseCount('order_payments', 1);
        $this->assertSame('preparing', $ticket->fresh()->status);
        $next = $this->open();
        $this->assertNotSame($order->id, $next->id);
        $this->service->advance($ticket->id, 'preparing');
        $this->assertSame('ready', $ticket->fresh()->status);
    }

    public function test_payment_rejects_unsubmitted_items_and_changed_total(): void
    {
        $order = $this->open();
        $this->add($order);
        try {
            $this->service->pay($order->id, 'manual', $order->totalCents());
            $this->fail('Draft payment allowed');
        } catch (ValidationException) {
        }
        $this->send($order);
        $this->expectException(ValidationException::class);
        $this->service->pay($order->id, 'cash', 1);
    }

    public function test_paid_order_cannot_be_modified(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $this->service->pay($order->id, 'manual', $order->totalCents());
        $this->expectException(ValidationException::class);
        $this->service->covers($order->id, 5);
    }

    public function test_cancel_retains_history_and_frees_table(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $this->service->cancel($order->id);
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(1, $order->items()->count());
        $this->assertDatabaseHas('order_events', ['order_id' => $order->id, 'action' => 'cancelled']);
        $this->assertNotSame($order->id, $this->open()->id);
    }

    public function test_cancel_is_blocked_after_production_starts(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $this->service->advance(ProductionTicket::first()->id, 'queued');
        $this->expectException(ValidationException::class);
        $this->service->cancel($order->id);
    }

    public function test_invisible_menu_and_foreign_ingredient_removal_are_rejected(): void
    {
        $order = $this->open();
        try {
            $this->add($order, removed: [$this->extra->id]);
            $this->fail('Foreign ingredient accepted');
        } catch (ValidationException) {
        }
        $this->category->menu->update(['is_visible' => false]);
        $this->expectException(ValidationException::class);
        $this->add($order);
    }

    public function test_routes_and_livewire_views_render_including_print_and_history(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        foreach (['orders.index', 'cash-desk.index', 'cash-desk.history', 'kds.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('orders.show', $order))->assertOk()->assertSee('Margherita');
        $this->get(route('cash-desk.show', $order))->assertOk()->assertSee('Registra incasso');
        $this->get(route('cash-desk.print', $order))->assertOk()->assertSee('Documento non fiscale');
        $this->get(route('kds.show', $this->pizza->department_id))->assertOk()->assertSee('Margherita');
    }

    public function test_waiter_can_use_orders_but_cannot_access_cash_or_advance_production(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('operator', 'web'));
        $user->givePermissionTo(Permissions::ORDERS);
        $this->actingAs($user);
        $this->get(route('orders.show', $order))->assertOk();
        $this->get(route('cash-desk.show', $order))->assertForbidden();
        $this->get(route('kds.index'))->assertForbidden();
        Livewire::test(Show::class, ['order' => $order])->assertForbidden();
    }

    public function test_editor_adds_products_and_cash_component_records_payment(): void
    {
        $order = $this->open();
        Livewire::test(Editor::class, ['order' => $order])->call('chooseProduct', $this->pizza->id)
            ->set('quantity', 2)->set('added', [$this->extra->id])->call('saveItem')->assertHasNoErrors()->assertSee('Margherita');
        $this->assertSame(2800, $order->totalCents());
        $this->send($order);
        Livewire::test(Show::class, ['order' => $order])->set('method', 'cash')->call('pay')->assertHasNoErrors()->assertSee('Pagamento registrato');
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_room_calendar_navigation_keeps_open_orders_visible(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-11-17 12:00:00'));
        $order = $this->open();
        $this->add($order);

        foreach ([RoomMap::class, \App\Livewire\CashDesk\RoomMap::class] as $component) {
            Livewire::test($component)
                ->assertSet('date', '2026-11-17')
                ->call('previousDay')->assertSet('date', '2026-11-16')
                ->assertViewHas('orders', fn ($orders) => $orders->contains('id', $order->id))
                ->call('nextDay')->assertSet('date', '2026-11-17')
                ->set('date', '2026-10-01')
                ->call('selectToday')->assertSet('date', '2026-11-17')
                ->assertHasNoErrors();
        }
    }

    public function test_open_room_map_order_does_not_write_on_get(): void
    {
        $this->get(route('orders.index'))->assertOk();
        $this->assertDatabaseCount('orders', 0);
        Livewire::test(RoomMap::class)->call('selectTable', $this->table->id)->call('openOrder')->assertHasNoErrors();
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_draft_ingredients_can_be_edited_without_repricing_the_base_product(): void
    {
        $order = $this->open();
        $this->add($order);
        $item = $order->items()->first();
        $this->category->products()->updateExistingPivot($this->pizza->id, ['menu_price' => '99.00']);
        Livewire::test(Editor::class, ['order' => $order])->call('editItem', $item->id)
            ->set('added', [$this->extra->id])->set('removed', [$this->tomato->id])
            ->call('saveItem')->assertHasNoErrors();
        $this->assertSame(1600, $order->totalCents());
        $this->assertSame(2, $item->modifiers()->count());
    }

    public function test_queued_note_edit_retains_original_modifiers_and_price(): void
    {
        $order = $this->open();
        $this->add($order, added: [$this->extra->id]);
        $this->send($order);
        $item = $order->items()->first();
        Livewire::test(Editor::class, ['order' => $order])->call('editItem', $item->id)->set('notes', 'Senza sale')
            ->set('quantity', 2)->call('saveItem')->assertHasNoErrors();
        $this->assertSame(2800, $order->totalCents());
        $this->assertSame(1, $item->modifiers()->count());
        $this->assertSame('Senza sale', $item->fresh()->notes);
    }

    public function test_active_orders_from_previous_days_remain_visible_in_both_maps(): void
    {
        $order = $this->open();
        $this->add($order);
        $order->update(['service_date' => today()->subDay()]);
        Livewire::test(RoomMap::class)->assertSee('Margherita');
        Livewire::test(\App\Livewire\CashDesk\RoomMap::class)->call('selectTable', $this->table->id)->assertRedirect(route('cash-desk.show', $order));
    }

    public function test_unrelated_booking_cannot_be_assigned_and_stock_is_not_changed(): void
    {
        $this->extra->update(['stock_quantity' => 100]);
        $order = $this->open();
        $this->add($order, added: [$this->extra->id]);
        $this->send($order);
        $this->assertSame('100.000', $this->extra->fresh()->stock_quantity);
        $otherTable = RoomTable::create(['room_id' => $this->table->room_id, 'name' => '20']);
        $booking = \App\Models\Booking::create(['booking_date' => today(), 'booking_time' => '20:00', 'pax' => 3, 'status' => 'accepted', 'source' => 'staff', 'language' => 'it']);
        $booking->tables()->attach($this->table);
        $this->expectException(ValidationException::class);
        $this->service->open($otherTable->id, 1, $booking->id);
    }

    public function test_role_permissions_are_rechecked_for_livewire_actions(): void
    {
        $order = $this->open();
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('operator', 'web'));
        $user->givePermissionTo(Permissions::ORDERS);
        $this->actingAs($user);
        $component = Livewire::test(Editor::class, ['order' => $order]);
        $user->revokePermissionTo(Permissions::ORDERS);
        $component->call('addCourse')->assertForbidden();
    }

    public function test_kds_can_only_advance_tickets_for_selected_department(): void
    {
        $order = $this->open();
        $this->add($order);
        $this->send($order);
        $other = Department::create(['name' => 'Bar', 'production' => true]);
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::test(Board::class, ['department' => $other->id])->call('advance', ProductionTicket::first()->id, 'queued');
    }
}
