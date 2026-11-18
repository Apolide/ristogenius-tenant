<?php

namespace Tests\Feature\PublicOrders;

use App\Livewire\PublicOrders\Menu;
use App\Models\Customer;
use App\Models\CustomerOrderAccess;
use App\Models\CustomerOrderClosure;
use App\Models\CustomerOrderNeighborhood;
use App\Models\CustomerOrderSchedule;
use App\Models\CustomerOrderSession;
use App\Models\Department;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\MessageOutbox;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\ProductionTicket;
use App\Models\Room;
use App\Models\RoomTable;
use App\Models\User;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use App\Services\PublicOrders\AvailabilityService;
use App\Services\PublicOrders\CheckoutService;
use App\Services\PublicOrders\CustomerAccessService;
use App\Services\PublicOrders\CustomerOrderWorkflow;
use App\Services\PublicOrders\OrderingSettings;
use App\Services\PublicOrders\TableQrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicOrderingTest extends TestCase
{
    use RefreshDatabase;

    private DigitalMenu $menu;

    private $category;

    private DigitalMenuProduct $product;

    private RoomTable $table;

    private CustomerOrderNeighborhood $area;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 16)->setTime(10, 0));
        RateLimiter::clear('order-access-ip:127.0.0.1');
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('operator', 'web');
        app(Permissions::class)->ensurePermissionsExist();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $room = Room::create(['name' => 'Sala blu', 'active' => true, 'service_charge' => '2.00', 'service_charge_percentage' => '10.00']);
        $this->table = RoomTable::create(['room_id' => $room->id, 'name' => '3', 'min_people' => 2]);
        $this->menu = DigitalMenu::create(['name' => ['it' => 'Menu cena', 'en' => 'Dinner'], 'slug' => 'cena', 'enabled_languages' => ['it', 'en'], 'is_published' => true, 'is_visible' => true]);
        $this->category = $this->menu->categories()->create(['name' => ['it' => 'Pizze'], 'is_enabled' => true]);
        $department = Department::create(['name' => 'Pizzeria', 'production' => true]);
        $this->product = DigitalMenuProduct::create(['name' => ['it' => 'Margherita'], 'price' => '8.00', 'vat' => 10, 'is_active' => true, 'department_id' => $department->id]);
        $this->category->products()->attach($this->product, ['menu_price' => '10.00', 'is_visible' => true]);
        $this->area = CustomerOrderNeighborhood::create(['name' => 'Centro']);
        app(OrderingSettings::class)->save(array_replace(app(OrderingSettings::class)->get(), ['enabled' => true, 'delivery_enabled' => true, 'delivery_fee_cents' => 300, 'delivery_minimum_cents' => 1000]));
        CustomerOrderSchedule::create(['weekday' => 1, 'opens_at' => '18:00', 'closes_at' => '23:00', 'pickup' => true, 'delivery' => true]);
    }

    private function line(array $extra = []): array
    {
        return array_replace(['category_id' => $this->category->id, 'product_id' => $this->product->id, 'quantity' => 1, 'notes' => 'Ben cotta', 'added' => [], 'removed' => []], $extra);
    }

    private function identity(string $email = 'mario@example.test', string $phone = '+393401005264'): array
    {
        return ['name' => 'Mario', 'email' => $email, 'phone' => $phone, 'privacy' => true];
    }

    private function token(?string $table = null, array $identity = []): string
    {
        $input = $identity ?: $this->identity();
        RateLimiter::clear('order-access:'.hash('sha256', '127.0.0.1|'.$input['email']));
        app(CustomerAccessService::class)->request($this->menu, 'it', $input, $table);
        $box = MessageOutbox::latest('created_at')->get()->first(fn ($b) => str_starts_with($b->deduplication_key, 'access:'));

        return basename(parse_url($box->payload['deliveries'][0]['url'], PHP_URL_PATH));
    }

    private function login(?string $table = null, array $identity = []): CustomerOrderSession
    {
        $this->withSession([]);
        $token = $this->token($table, $identity);
        $this->post(route('public-orders.redeem', $token))->assertRedirect();

        return app(CustomerAccessService::class)->current($table);
    }

    private function details(string $type = 'pickup'): array
    {
        return ['type' => $type, 'date' => '2026-11-16', 'time' => '20:00', 'address' => 'Via Roma 1', 'neighborhood' => $this->area->id, 'notes' => 'Citofono Mario'];
    }

    private function submit(string $key = 'submission-1', string $type = 'pickup', ?string $table = null): OrderCourse
    {
        return app(CheckoutService::class)->submit([$this->line()], 'it', $table, $this->details($type), $key, $type === 'delivery' ? 1300 : ($table ? 1100 : 1000));
    }

    public function test_consultation_remains_available_but_cart_requires_email_verification(): void
    {
        $this->get(route('menu.public', ['language' => 'it', 'menu' => $this->menu]))->assertOk()->assertSee('Ordina per ritiro');
        $this->get(route('public-orders.menu', ['language' => 'it', 'menu' => $this->menu]))->assertOk()->assertSee('Ricevi il link')->assertDontSee('Personalizza e aggiungi');
        Livewire::test(Menu::class, ['language' => 'it', 'menu' => $this->menu])->call('chooseProduct', $this->category->id, $this->product->id)->assertForbidden();
    }

    public function test_email_link_is_one_time_and_get_does_not_consume_it(): void
    {
        $token = $this->token();
        $this->assertSame(0, Customer::count());
        $this->get(route('public-orders.verify', $token))->assertOk()->assertSee('Conferma e apri');
        $this->assertNull(CustomerOrderAccess::first()->consumed_at);
        $this->post(route('public-orders.redeem', $token))->assertRedirect();
        $this->assertNotNull(app(CustomerAccessService::class)->current());
        $this->assertNotNull(CustomerOrderAccess::first()->consumed_at);
        $this->post(route('public-orders.redeem', $token))->assertStatus(410);
        $this->assertSame(1, Customer::count());
        $this->assertStringNotContainsString('mario@example.test', DB::table('customer_order_accesses')->value('identity'));
    }

    public function test_expired_link_is_rejected_without_creating_customer(): void
    {
        $token = $this->token();
        $this->travel(21)->minutes();
        $this->post(route('public-orders.redeem', $token))->assertStatus(410);
        $this->assertSame(0, Customer::count());
    }

    public function test_recognition_only_greets_and_does_not_authenticate(): void
    {
        Customer::create(['firstname' => 'Mario', 'email' => 'mario@example.test', 'phone' => '+393401005264', 'registration_source' => 'test']);
        $this->withCookie('ordering_recognition', '+393401005264')->get(route('public-orders.menu', ['language' => 'it', 'menu' => $this->menu]))->assertOk()->assertSee('Ciao Mario')->assertDontSee('Personalizza e aggiungi');
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->submit();
    }

    public function test_auth_conflicting_phone_cannot_overwrite_another_customer(): void
    {
        $customer = Customer::create(['firstname' => 'Altro', 'email' => 'altro@example.test', 'phone' => '+393401005264', 'registration_source' => 'test']);
        $token = $this->token();
        $this->post(route('public-orders.redeem', $token))->assertSessionHasErrors('identity');
        $this->assertSame('altro@example.test', $customer->fresh()->email);
        $this->assertSame(0, CustomerOrderSession::count());
    }

    public function test_blacklist_and_unpublished_menu_prevent_access(): void
    {
        Customer::create(['firstname' => 'Mario', 'email' => 'mario@example.test', 'phone' => '+393401005264', 'registration_source' => 'test', 'blacklisted' => true]);
        $this->post(route('public-orders.redeem', $this->token()))->assertForbidden();
        $this->menu->update(['is_published' => false]);
        $this->get(route('public-orders.menu', ['language' => 'it', 'menu' => $this->menu]))->assertNotFound();
    }

    public function test_remote_submission_is_idempotent_has_no_table_and_waits_for_acceptance(): void
    {
        $this->login();
        $course = $this->submit();
        $same = $this->submit();
        $order = $course->order;
        $this->assertSame($course->id, $same->id);
        $this->assertSame('pickup', $order->fulfillment);
        $this->assertSame('received', $order->fulfillment_status);
        $this->assertSame(0, $order->tables()->count());
        $this->assertSame(1000, $order->totalCents());
        $this->assertSame(0, ProductionTicket::count());
        $this->assertSame(1, Order::count());
        $this->assertDatabaseHas('message_outboxes', ['aggregate_type' => 'customer_order', 'deduplication_key' => 'order:'.$order->id.':received']);
    }

    public function test_delivery_fee_minimum_free_threshold_and_historical_address(): void
    {
        $this->login();
        $course = $this->submit(type: 'delivery');
        $order = $course->order;
        $this->assertSame(1300, $order->totalCents());
        $this->assertSame('Centro', $order->delivery_neighborhood);
        $this->area->delete();
        $this->product->update(['price' => 999]);
        $this->assertSame(1300, $order->totalCents());
        $this->assertSame('Via Roma 1', $order->delivery_address);
        $settings = app(OrderingSettings::class)->get();
        $settings['delivery_free_over_cents'] = 1000;
        app(OrderingSettings::class)->save($settings);
        $this->area = CustomerOrderNeighborhood::create(['name' => 'Nuovo']);
        $data = $this->details('delivery');
        $free = app(CheckoutService::class)->submit([$this->line()], 'it', null, $data, 'free', 1000);
        $this->assertSame(0, $free->order->delivery_fee_cents);
    }

    public function test_closed_slot_and_insufficient_minimum_are_revalidated_at_submit(): void
    {
        $this->login();
        CustomerOrderClosure::create(['starts_at' => '2026-11-16 19:00', 'ends_at' => '2026-11-16 21:00', 'type' => 'pickup']);
        try {
            $this->submit();
            $this->fail('Closed slot accepted');
        } catch (ValidationException) {
        }
        $settings = app(OrderingSettings::class)->get();
        $settings['delivery_minimum_cents'] = 2000;
        app(OrderingSettings::class)->save($settings);
        $this->expectException(ValidationException::class);
        $this->submit(type: 'delivery');
    }

    public function test_overnight_schedules_and_channel_specific_closures(): void
    {
        CustomerOrderSchedule::create(['weekday' => 1, 'opens_at' => '22:00', 'closes_at' => '02:00', 'pickup' => true, 'delivery' => false]);
        $slots = app(AvailabilityService::class)->slots('2026-11-17', 'pickup');
        $this->assertContains('00:15', $slots);
        $this->assertNotContains('02:00', $slots);
        $this->assertSame([], app(AvailabilityService::class)->slots('2026-11-17', 'delivery'));
        CustomerOrderClosure::create(['starts_at' => '2026-11-17 00:00', 'ends_at' => '2026-11-17 01:00', 'type' => 'pickup']);
        $this->assertNotContains('00:15', app(AvailabilityService::class)->slots('2026-11-17', 'pickup'));
    }

    public function test_tampered_prices_quantities_and_forbidden_takeaway_product_are_rejected(): void
    {
        $this->login();
        try {
            app(CheckoutService::class)->submit([$this->line(['unit_price_cents' => 1])], 'it', null, $this->details(), 'bad-price', 1);
            $this->fail('Tampered total accepted');
        } catch (ValidationException) {
        }
        try {
            app(CheckoutService::class)->quote([$this->line(['quantity' => -1])], 'pickup', 'it');
            $this->fail('Negative quantity accepted');
        } catch (ValidationException) {
        }
        $this->product->update(['disallow_takeaway' => true]);
        $this->expectException(ValidationException::class);
        $this->submit();
    }

    public function test_personalization_and_notes_reach_kds_after_staff_starts_preparation(): void
    {
        $this->login();
        $ingredient = DigitalMenuIngredient::create(['name' => ['it' => 'Burrata'], 'add_price' => '2.50']);
        $course = app(CheckoutService::class)->submit([$this->line(['added' => [$ingredient->id]])], 'it', null, $this->details(), 'customized', 1250);
        $order = $course->order;
        $this->actingAs($this->admin);
        app(CustomerOrderWorkflow::class)->transition($order->id, 'accept', 'received');
        $this->assertSame(0, ProductionTicket::count());
        app(CustomerOrderWorkflow::class)->transition($order->id, 'prepare', 'accepted');
        $ticket = ProductionTicket::first();
        $this->assertNotNull($ticket);
        $this->assertSame('Ben cotta', $ticket->items()->first()->notes);
        $this->assertSame('Burrata', $ticket->items()->first()->modifiers()->first()->ingredient_name);
        app(OrderService::class)->advance($ticket->id, 'queued');
        app(OrderService::class)->advance($ticket->id, 'preparing');
        app(CustomerOrderWorkflow::class)->transition($order->id, 'ready', 'preparing');
        app(CustomerOrderWorkflow::class)->transition($order->id, 'complete', 'ready');
        $this->assertSame('completed', $order->fresh()->fulfillment_status);
        $this->assertSame('served', $ticket->fresh()->status);
    }

    public function test_paid_remote_order_can_still_progress_and_cannot_be_paid_twice(): void
    {
        $this->login();
        $order = $this->submit()->order;
        $this->actingAs($this->admin);
        app(CustomerOrderWorkflow::class)->transition($order->id, 'accept', 'received');
        app(OrderService::class)->pay($order->id, 'cash', 1000);
        app(OrderService::class)->pay($order->id, 'cash', 1000);
        $this->assertSame(1, $order->payments()->count());
        app(CustomerOrderWorkflow::class)->transition($order->id, 'prepare', 'accepted');
        $this->assertSame('preparing', $order->fresh()->fulfillment_status);
    }

    public function test_table_orders_merge_with_staff_order_without_duplicate_covers(): void
    {
        $this->actingAs($this->admin);
        $order = app(OrderService::class)->open($this->table->id, 2);
        $this->app['auth']->forgetGuards();
        $this->login($this->table->ordering_token);
        $course = $this->submit(type: 'dine_in', table: $this->table->ordering_token);
        $this->assertSame($order->id, $course->order_id);
        $this->assertSame(1500, $order->totalCents());
        $this->assertSame(1000, $course->service_percentage_bps);
        $this->assertSame(100, $order->serviceFeeCents());
        $this->assertSame(1, Order::count());
        $this->assertSame('Citofono Mario', $course->customer_notes);
        $this->table->room->update(['service_charge_percentage' => 50]);
        $this->assertSame(100, $order->serviceFeeCents());
    }

    public function test_partial_cash_payment_keeps_table_busy_and_blocks_more_qr_orders(): void
    {
        $access = $this->login($this->table->ordering_token);
        $order = $access->order;
        $this->actingAs($this->admin);
        app(OrderService::class)->pay($order->id, 'cash', $order->totalCents(), 100);
        $this->assertDatabaseHas('order_tables', ['order_id' => $order->id, 'active_table_id' => $this->table->id]);
        try {
            $this->submit(key: 'after-partial', type: 'dine_in', table: $this->table->ordering_token);
            $this->fail('Expected frozen QR account');
        } catch (ValidationException) {
            $this->assertSame(0, $order->items()->count());
        }
        app(OrderService::class)->pay($order->id, 'card', $order->totalCents());
        $this->assertDatabaseHas('order_tables', ['order_id' => $order->id, 'active_table_id' => null]);
    }

    public function test_discount_survives_customer_proposal_quote_and_summary(): void
    {
        $this->login();
        $order = $this->submit()->order;
        $this->actingAs($this->admin);
        app(OrderService::class)->discount($order->id, 150, 'Promozione', 1000);
        $order->refresh();
        $service = app(\App\Services\PublicOrders\OrderProposalService::class);
        $quote = $service->quote($order, $service->initialLines($order), $this->details());
        $this->assertSame(850, $quote['total']);
        $summary = app(\App\Services\PublicOrders\OrderSummaryService::class)->proposed($order, $quote);
        $this->assertSame(150, $summary['discount_cents']);
        $this->assertSame(850, $summary['total_cents']);
    }

    public function test_closed_table_session_cannot_add_to_next_visit(): void
    {
        $access = $this->login($this->table->ordering_token);
        $order = $access->order;
        $this->actingAs($this->admin);
        app(OrderService::class)->cancel($order->id);
        $this->assertNull(app(CustomerAccessService::class)->current($this->table->ordering_token));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->submit(type: 'dine_in', table: $this->table->ordering_token);
    }

    public function test_qr_rotation_invalidates_previous_link_and_customer_session(): void
    {
        $old = $this->table->ordering_token;
        $this->login($old);
        $this->actingAs($this->admin);
        Livewire::test(\App\Livewire\Settings\Rooms\OrderTableQrCodes::class)->call('regenerate', $this->table->id)->assertHasNoErrors();
        $this->assertNull(app(CustomerAccessService::class)->current($old));
        $this->get(route('public-orders.table', ['language' => 'it', 'tableToken' => $old]))->assertNotFound();
    }

    public function test_table_context_cannot_be_changed_to_another_table(): void
    {
        $this->login($this->table->ordering_token);
        $other = RoomTable::create(['room_id' => $this->table->room_id, 'name' => '4']);
        $this->assertNull(app(CustomerAccessService::class)->current($other->ordering_token));
        $this->assertNull(app(CustomerAccessService::class)->current());
    }

    public function test_public_component_can_build_cart_only_after_verification_and_submit(): void
    {
        $this->login();
        Livewire::test(Menu::class, ['language' => 'it', 'menu' => $this->menu])->call('chooseProduct', $this->category->id, $this->product->id)->set('quantity', 2)->call('addLine')->assertHasNoErrors()->set('time', '20:00')->call('submit')->assertHasNoErrors()->assertSee('Ordine ricevuto');
        $this->assertSame(2000, Order::first()->totalCents());
    }

    public function test_all_new_backend_pages_print_and_qr_render(): void
    {
        $this->login();
        $order = $this->submit()->order;
        $this->actingAs($this->admin);
        foreach (['customers-orders.index', 'settings.customer-orders', 'settings.rooms.order-qr-codes', 'cash-desk.customer-orders'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('customers-orders.show', $order))->assertOk()->assertSee($order->reference);
        $this->get(route('customers-orders.print', $order))->assertOk()->assertSee('Documento non fiscale');
        $this->get(route('settings.rooms.order-qr-download', $this->table))->assertOk()->assertHeader('Content-Type', 'image/svg+xml')->assertSee('Sala blu');
        $this->assertStringContainsString('<svg', app(TableQrService::class)->svg($this->table));
    }

    public function test_staff_permission_separation_and_livewire_action_authorization(): void
    {
        $this->login();
        $order = $this->submit()->order;
        $operator = User::factory()->create();
        $operator->assignRole('operator');
        $operator->givePermissionTo(Permissions::CUSTOMER_ORDERS);
        $this->actingAs($operator);
        $this->get(route('customers-orders.index'))->assertOk();
        $this->get(route('settings.customer-orders'))->assertForbidden();
        $this->get(route('cash-desk.customer-orders'))->assertForbidden();
        Livewire::test(\App\Livewire\CustomerOrders\Show::class, ['order' => $order])->call('pay')->assertForbidden();
    }

    public function test_numeric_string_settings_allow_public_menu_and_correct_slots(): void
    {
        $this->travelTo(now()->setTime(17, 30));
        $settings = array_replace(app(OrderingSettings::class)->get(), [
            'lead_minutes' => '45', 'preparation_minutes' => '30',
            'slot_minutes' => '15', 'advance_days' => '2',
            'delivery_fee_cents' => '300', 'delivery_minimum_cents' => '1000',
            'delivery_free_over_cents' => '5000',
        ]);
        // Reproduce values already saved by HTML/Livewire number inputs.
        app(\App\Services\Settings\TenantSettingsService::class)->updateSection('customer_orders', $settings);

        $this->get(route('public-orders.menu', ['language' => 'it', 'menu' => $this->menu]))->assertOk();
        $slots = app(AvailabilityService::class)->slots('2026-11-16', 'pickup');
        $this->assertSame('18:15', $slots[0]);
        $this->assertSame('18:30', $slots[1]);
        $this->assertSame([], app(AvailabilityService::class)->slots('2026-11-23', 'pickup'));
        foreach (['lead_minutes', 'preparation_minutes', 'slot_minutes', 'advance_days', 'delivery_fee_cents', 'delivery_minimum_cents', 'delivery_free_over_cents'] as $key) {
            $this->assertIsInt(app(OrderingSettings::class)->get()[$key]);
        }
    }

    public function test_settings_form_saves_numeric_inputs_as_integers(): void
    {
        $this->actingAs($this->admin);
        Livewire::test(\App\Livewire\Settings\CustomerOrders\Settings::class)
            ->set('settings.lead_minutes', '45')
            ->set('settings.preparation_minutes', '30')
            ->set('settings.slot_minutes', '15')
            ->set('settings.advance_days', '2')
            ->call('save')->assertHasNoErrors();

        $saved = app(\App\Services\Settings\TenantSettingsService::class)->profile()->fresh()->settings['customer_orders'];
        $this->assertSame(45, $saved['lead_minutes']);
        $this->assertSame(30, $saved['preparation_minutes']);
        $this->assertSame(15, $saved['slot_minutes']);
        $this->assertSame(2, $saved['advance_days']);
    }

    public function test_settings_crud_preserves_reservation_configuration(): void
    {
        $this->actingAs($this->admin);
        $old = app(\App\Services\Settings\TenantSettingsService::class)->settings()['reservations'];
        $component = Livewire::test(\App\Livewire\Settings\CustomerOrders\Settings::class)
            ->set('fee', '4.50')->set('minimum', '15.00')->call('save')->assertHasNoErrors();
        $this->assertSame(450, app(OrderingSettings::class)->get()['delivery_fee_cents']);
        $this->assertSame($old, app(\App\Services\Settings\TenantSettingsService::class)->settings()['reservations']);
        $component->set('areaName', 'Nord')->call('addArea')->assertHasNoErrors();
        $area = CustomerOrderNeighborhood::where('name', 'Nord')->firstOrFail();
        $component->call('editArea', $area->id)->set('areaName', 'Nord nuovo')->call('addArea')->assertHasNoErrors();
        $this->assertSame('Nord nuovo', $area->fresh()->name);
        $schedule = CustomerOrderSchedule::first();
        $component->call('editSchedule', $schedule->id)->set('opens', '19:00')->call('addSchedule')->assertHasNoErrors();
        $this->assertSame('19:00:00', $schedule->fresh()->opens_at);
        $component->set('starts', '2026-11-18T18:00')->set('ends', '2026-11-18T23:00')->call('addClosure')->assertHasNoErrors();
        $closure = CustomerOrderClosure::firstOrFail();
        $component->call('editClosure', $closure->id)->assertSet('starts', '2026-11-18T18:00')->set('reason', 'Ferie')->call('addClosure')->assertHasNoErrors();
        $this->assertSame('Ferie', $closure->fresh()->reason);
    }

    public function test_cash_keeps_completed_unpaid_remote_orders_visible_from_previous_days(): void
    {
        $this->login();
        $order = $this->submit()->order;
        $order->update(['fulfillment_status' => 'completed', 'service_date' => today()->subDay()]);
        $this->actingAs($this->admin);
        Livewire::test(\App\Livewire\CashDesk\CustomerOrders::class)->assertSee($order->reference);
        Livewire::test(\App\Livewire\CustomerOrders\Index::class)->assertDontSee($order->reference);
    }

    public function test_personalizations_keep_existing_prices_when_staff_edits_a_public_course(): void
    {
        $ingredient = DigitalMenuIngredient::create(['name' => ['it' => 'Burrata'], 'add_price' => '2.50']);
        $access = $this->login($this->table->ordering_token);
        $course = app(CheckoutService::class)->submit([$this->line(['added' => [$ingredient->id]])], 'it', $this->table->ordering_token, $this->details('dine_in'), 'ingredient-snapshot', 1375);
        $item = $course->items()->first();
        $ingredient->update(['add_price' => '99.00']);
        $this->actingAs($this->admin);
        app(OrderService::class)->saveItem($access->order_id, $course->id, '', '', 2, 'Cotta bene', [$ingredient->id], [], $item->id);
        $this->assertSame(1250, $item->fresh()->unit_price_cents);
        $this->assertSame(250, $course->order->serviceFeeCents());
    }

    public function test_order_lifecycle_rejects_stale_actions_and_ready_before_kds_completion(): void
    {
        $this->login();
        $order = $this->submit()->order;
        $this->actingAs($this->admin);
        app(CustomerOrderWorkflow::class)->transition($order->id, 'accept', 'received');
        try {
            app(CustomerOrderWorkflow::class)->transition($order->id, 'accept', 'received');
            $this->fail('Duplicate transition accepted');
        } catch (ValidationException) {
        }
        app(CustomerOrderWorkflow::class)->transition($order->id, 'prepare', 'accepted');
        $this->expectException(ValidationException::class);
        app(CustomerOrderWorkflow::class)->transition($order->id, 'ready', 'preparing');
    }

    public function test_expired_sessions_are_rejected_and_pruned_without_touching_orders(): void
    {
        $access = $this->login();
        $order = $this->submit()->order;
        $access->update(['expires_at' => now()->subDays(2)]);
        $this->assertNull(app(CustomerAccessService::class)->current());
        $this->artisan('customer-orders:prune-access')->assertSuccessful();
        $this->assertNull($access->fresh());
        $this->assertNotNull($order->fresh());
    }

    public function test_rejected_table_submission_is_preserved_but_not_charged(): void
    {
        $access = $this->login($this->table->ordering_token);
        $course = $this->submit(type: 'dine_in', table: $this->table->ordering_token);
        $this->assertSame(1500, $course->order->totalCents());
        $this->actingAs($this->admin);
        app(OrderService::class)->rejectCourse($access->order_id, $course->id);
        $this->assertNotNull($course->fresh()->rejected_at);
        $this->assertSame(1, $course->items()->count());
        $this->assertSame(400, $course->order->totalCents());
        $this->assertSame(0, $course->order->serviceFeeCents());
        app(OrderService::class)->pay($access->order_id, 'cash', 400);
        $this->assertDatabaseHas('message_outboxes', ['deduplication_key' => 'course:'.$course->id.':rejected']);
    }

    public function test_table_submission_cannot_be_rejected_after_sending_to_production(): void
    {
        $access = $this->login($this->table->ordering_token);
        $course = $this->submit(type: 'dine_in', table: $this->table->ordering_token);
        $this->actingAs($this->admin);
        app(OrderService::class)->send($access->order_id, $course->id);
        $this->expectException(ValidationException::class);
        app(OrderService::class)->rejectCourse($access->order_id, $course->id);
    }

    public function test_email_requests_are_throttled_and_do_not_create_customers_before_verification(): void
    {
        RateLimiter::clear('order-access:'.hash('sha256', '127.0.0.1|mario@example.test'));
        for ($i = 0; $i < 3; $i++) {
            app(CustomerAccessService::class)->request($this->menu, 'it', $this->identity());
        }
        $this->assertSame(0, Customer::count());
        $this->assertSame(3, CustomerOrderAccess::count());
        $this->expectException(ValidationException::class);
        app(CustomerAccessService::class)->request($this->menu, 'it', $this->identity());
    }

    public function test_unredeemed_table_link_does_not_attach_to_the_next_visit(): void
    {
        $this->actingAs($this->admin);
        $old = app(OrderService::class)->open($this->table->id, 2);
        $token = $this->token($this->table->ordering_token);
        app(OrderService::class)->cancel($old->id);
        $this->post(route('public-orders.redeem', $token))->assertStatus(410);
        $this->assertSame(1, Order::count());
        $this->assertSame(0, CustomerOrderSession::count());
    }

    public function test_customer_order_emails_use_existing_outbox_pipeline(): void
    {
        $this->token();
        $outbox = MessageOutbox::where('aggregate_type', 'customer_order')->first();
        Queue::fake();
        app(\App\Services\Messaging\MessageDispatcher::class)->dispatch($outbox);
        Queue::assertPushed(\App\Jobs\Messaging\SendMessageChannelJob::class, fn ($job) => $job->channel === 'email');
        Mail::fake();
        app(\App\Services\Messaging\Channels\EmailMessageChannel::class)->send($outbox);
        Mail::assertSent(\App\Mail\CustomerOrderMail::class, fn ($mail) => $mail->hasTo('mario@example.test'));
    }
}
