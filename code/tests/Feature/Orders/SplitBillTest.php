<?php

namespace Tests\Feature\Orders;

use App\Livewire\CashDesk\FiscalDocuments;
use App\Livewire\CashDesk\Show;
use App\Models\CashBillPlan;
use App\Models\CashBillShare;
use App\Models\CashPaymentBatch;
use App\Models\Order;
use App\Models\Room;
use App\Models\RoomTable;
use App\Models\User;
use App\Services\Orders\CashBillService;
use App\Services\Orders\CashCheckoutService;
use App\Services\Orders\CashShiftService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SplitBillTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user);
        app(Permissions::class)->ensurePermissionsExist();
    }

    private function order(int $cents = 3000): Order
    {
        return Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 1, 'cover_price_cents' => $cents]);
    }

    private function plan(Order $order, string $mode = 'equal', int $count = 3): CashBillPlan
    {
        return app(CashBillService::class)->create($order->id, $mode, $count, $order->fresh()->totalCents(), $order->fresh()->balanceCents());
    }

    private function pay(Order $order, array $amounts, ?CashBillShare $share = null, ?string $key = null, array $extra = []): CashPaymentBatch
    {
        $order->refresh();

        return app(CashCheckoutService::class)->pay($order->id, $key ?? (string) Str::uuid(), $amounts, $order->totalCents(), $order->balanceCents(),
            $share?->id, $order->billPlan()->first()?->version, ...$extra);
    }

    private function rejected(callable $action): void
    {
        try {
            $action();
            $this->fail('Expected a rejected checkout mutation');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }
    }

    private function products(): array
    {
        $order = $this->order(100);
        $order->update(['covers' => 2]);
        $course = $order->courses()->create(['number' => 1]);
        $pizza = $course->items()->create(['product_name' => 'Pizza', 'menu_name' => 'Menu', 'quantity' => 2, 'base_price_cents' => 1000, 'unit_price_cents' => 1000, 'vat' => 10]);
        $drink = $course->items()->create(['product_name' => 'Bibita', 'menu_name' => 'Menu', 'quantity' => 1, 'base_price_cents' => 500, 'unit_price_cents' => 500, 'vat' => 22]);
        app(OrderService::class)->discount($order->id, 1000, 'Promozione 10%', 2700, true);

        return [$order->fresh(), $pizza, $drink];
    }

    public function test_shift_condition_renders_only_the_correct_branch_without_blade_text(): void
    {
        $order = $this->order();
        $this->get(route('cash-desk.show', $order))->assertOk()->assertSee('Nessun turno aperto')->assertDontSee('Turno di cassa aperto il')->assertDontSee('@else');
        app(CashShiftService::class)->open(0);
        $this->get(route('cash-desk.show', $order))->assertOk()->assertSee('Turno di cassa aperto il')->assertDontSee('Nessun turno aperto')->assertDontSee('@else');
    }

    public function test_equal_split_creates_all_shares_and_persists_rounding_after_reload(): void
    {
        $order = $this->order(1001);
        Livewire::test(Show::class, ['order' => $order])->set('shares', 3)->call('split')->assertHasNoErrors()->assertSee('Pagante 1')->assertSee('Pagante 3');
        $plan = $order->billPlan()->firstOrFail();
        $this->assertSame([334, 334, 333], $plan->shares()->pluck('amount_cents')->all());
        $this->pay($order, ['cash' => 100, 'card' => 234, 'voucher' => 0], $plan->shares()->first());
        Livewire::test(Show::class, ['order' => $order])->assertSee('Pagato')->assertSee('Pagante 3');
        $this->assertSame(667, $order->fresh()->balanceCents());
        $this->assertSame([334, 334, 333], $plan->shares()->pluck('amount_cents')->all());
    }

    public function test_mixed_payment_is_atomic_idempotent_and_reconciles_cash_tips_and_vouchers(): void
    {
        $shift = app(CashShiftService::class)->open(5000);
        $order = $this->order(3000);
        $key = (string) Str::uuid();
        $amounts = ['cash' => 1000, 'card' => 1500, 'voucher' => 500];
        $extra = ['payer' => 'Mario', 'tip' => 200, 'tipMethod' => 'cash', 'voucherIssuer' => 'Emittente', 'voucherQuantity' => 2];
        $batch = $this->pay($order, $amounts, key: $key, extra: $extra);
        $this->pay($order, $amounts, key: $key, extra: $extra);
        $this->assertDatabaseCount('cash_payment_batches', 1);
        $this->assertDatabaseCount('order_payments', 3);
        $this->assertSame(3000, (int) $order->payments()->sum('amount_cents'));
        $this->assertSame(200, (int) $order->payments()->sum('tip_cents'));
        $this->assertSame(6200, $shift->expectedCents());
        $this->assertSame(2, (int) $order->payments()->where('method', 'voucher')->value('voucher_quantity'));
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame($batch->id, $order->payments()->first()->cash_payment_batch_id);
        $this->assertSame(1, $order->events()->where('action', 'paid')->count());
    }

    public function test_invalid_voucher_or_wrong_share_sum_creates_no_partial_payment(): void
    {
        $order = $this->order();
        $share = $this->plan($order)->shares()->first();
        $this->rejected(fn () => $this->pay($order, ['cash' => 500, 'card' => 400, 'voucher' => 100], $share));
        $this->rejected(fn () => $this->pay($order, ['cash' => 500, 'card' => 400, 'voucher' => 0], $share));
        $this->assertDatabaseCount('order_payments', 0);
        $this->assertDatabaseCount('cash_payment_batches', 0);
        $this->assertNull($order->fresh()->cash_snapshot);
    }

    public function test_same_id_with_changed_payload_and_a_paid_share_with_a_new_id_are_rejected(): void
    {
        $order = $this->order();
        $share = $this->plan($order)->shares()->first();
        $key = (string) Str::uuid();
        $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], $share, $key);
        $this->rejected(fn () => $this->pay($order, ['cash' => 0, 'card' => 1000, 'voucher' => 0], $share, $key));
        $this->rejected(fn () => $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], $share));
        $this->assertDatabaseCount('order_payments', 1);
    }

    public function test_stale_modal_balance_and_plan_version_are_rejected(): void
    {
        $order = $this->order();
        $plan = $this->plan($order);
        $shares = $plan->shares()->get();
        $oldVersion = $plan->version;
        $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], $shares[0]);
        $this->rejected(fn () => app(CashCheckoutService::class)->pay($order->id, (string) Str::uuid(), ['cash' => 1000, 'card' => 0, 'voucher' => 0], 3000, 3000, $shares[1]->id, $oldVersion));
        $this->rejected(fn () => app(CashCheckoutService::class)->pay($order->id, (string) Str::uuid(), ['cash' => 1000, 'card' => 0, 'voucher' => 0], 3000, 2000, $shares[1]->id, $oldVersion));
        $this->assertDatabaseCount('order_payments', 1);
    }

    public function test_products_have_individual_totals_and_remainder_tracks_only_unassigned_units(): void
    {
        [$order, $pizza, $drink] = $this->products();
        $plan = $this->plan($order, 'items', 2);
        [$one, $two, $remainder] = $plan->shares()->get()->all();
        app(CashBillService::class)->assign($order->id, $one->id, [$pizza->id => 1, 'covers' => 1], 'Mario', $plan->fresh()->version);
        $this->assertSame(990, $one->fresh()->amount_cents);
        $this->assertSame(1440, $remainder->fresh()->amount_cents);
        $this->pay($order, ['cash' => 500, 'card' => 490, 'voucher' => 0], $one);
        app(CashBillService::class)->assign($order->id, $two->id, [$drink->id => 1, 'covers' => 1], 'Anna', $plan->fresh()->version);
        $this->assertSame(540, $two->fresh()->amount_cents);
        $this->assertSame(900, $remainder->fresh()->amount_cents);
        $this->rejected(fn () => app(CashBillService::class)->assign($order->id, $one->id, [], 'Mario', $plan->fresh()->version));
        $this->pay($order, ['cash' => 0, 'card' => 540, 'voucher' => 0], $two);
        $this->assertSame(900, $order->fresh()->balanceCents());
        $this->pay($order, ['cash' => 900, 'card' => 0, 'voucher' => 0], $remainder);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(2430, (int) $order->payments()->sum('amount_cents'));
    }

    public function test_products_cannot_be_assigned_twice_and_paid_remainder_cannot_be_changed(): void
    {
        [$order, $pizza] = $this->products();
        $plan = $this->plan($order, 'items', 2);
        [$one, $two, $remainder] = $plan->shares()->get()->all();
        app(CashBillService::class)->assign($order->id, $one->id, [$pizza->id => 2], 'Mario', $plan->version);
        $this->rejected(fn () => app(CashBillService::class)->assign($order->id, $two->id, [$pizza->id => 1], 'Anna', $plan->fresh()->version));
        $this->assertSame(0, $two->fresh()->amount_cents);
        $this->pay($order, ['cash' => 630, 'card' => 0, 'voucher' => 0], $remainder->fresh());
        $this->rejected(fn () => app(CashBillService::class)->assign($order->id, $one->id, [$pizza->id => 1], 'Mario', $plan->fresh()->version));
        $this->assertSame(1800, $one->fresh()->amount_cents);
    }

    public function test_editing_unpaid_assignments_releases_units_and_maintains_total(): void
    {
        [$order, $pizza] = $this->products();
        $plan = $this->plan($order, 'items', 2);
        [$one, $two, $remainder] = $plan->shares()->get()->all();
        app(CashBillService::class)->assign($order->id, $one->id, [$pizza->id => 2], 'Mario', $plan->version);
        app(CashBillService::class)->assign($order->id, $one->id, [$pizza->id => 1], 'Mario', $plan->fresh()->version);
        app(CashBillService::class)->assign($order->id, $two->id, [$pizza->id => 1], 'Anna', $plan->fresh()->version);
        $this->assertSame(900, $one->fresh()->amount_cents);
        $this->assertSame(900, $two->fresh()->amount_cents);
        $this->assertSame(630, $remainder->fresh()->amount_cents);
        $this->assertSame($order->totalCents(), (int) $plan->shares()->sum('amount_cents'));
    }

    public function test_account_changes_require_rebuilding_shares_before_any_money_is_recorded(): void
    {
        $order = $this->order(3000);
        $plan = $this->plan($order);
        $share = $plan->shares()->first();
        app(OrderService::class)->covers($order->id, 2);
        $this->rejected(fn () => $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], $share));
        $new = $this->plan($order);
        $this->assertSame([2000, 2000, 2000], $new->shares()->pluck('amount_cents')->all());
        $this->assertDatabaseCount('order_payments', 0);
    }

    public function test_plan_can_be_replaced_or_removed_only_before_the_first_share_is_paid(): void
    {
        $order = $this->order();
        $plan = $this->plan($order);
        app(CashBillService::class)->remove($order->id, $plan->version);
        $this->assertNull($order->billPlan()->first());
        $plan = $this->plan($order);
        $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], $plan->shares()->first());
        $this->rejected(fn () => $this->plan($order, 'equal', 2));
        $this->rejected(fn () => app(CashBillService::class)->remove($order->id, $plan->fresh()->version));
    }

    public function test_legacy_and_generic_payments_cannot_bypass_a_split_plan(): void
    {
        $order = $this->order();
        $this->plan($order);
        $this->rejected(fn () => app(OrderService::class)->pay($order->id, 'cash', 3000));
        $this->rejected(fn () => $this->pay($order, ['cash' => 3000, 'card' => 0, 'voucher' => 0]));
        $this->assertDatabaseCount('order_payments', 0);
    }

    public function test_previous_partial_receipts_allow_equal_shares_but_not_item_attribution(): void
    {
        $order = $this->order();
        $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0]);
        $this->assertSame('open', $order->fresh()->status);
        $this->rejected(fn () => $this->plan($order, 'items', 2));
        $plan = $this->plan($order, 'equal', 2);
        $this->assertSame(1000, (int) $plan->previous_paid_cents);
        $this->assertSame([1000, 1000], $plan->shares()->pluck('amount_cents')->all());
    }

    public function test_idempotency_keys_are_protected_across_old_and_new_payment_paths(): void
    {
        $order = $this->order();
        $key = (string) Str::uuid();
        $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], key: $key);
        $this->rejected(fn () => app(OrderService::class)->pay($order->id, 'cash', 3000, 1000, key: $key));
        $secondKey = (string) Str::uuid();
        app(OrderService::class)->pay($order->id, 'card', 3000, 500, key: $secondKey);
        $this->rejected(fn () => $this->pay($order, ['cash' => 500, 'card' => 0, 'voucher' => 0], key: $secondKey));
        $this->assertSame(1500, $order->fresh()->balanceCents());
    }

    public function test_first_share_does_not_release_the_table_but_the_last_one_does(): void
    {
        $room = Room::create(['name' => 'Sala', 'active' => true, 'service_charge' => '2.50']);
        $table = RoomTable::create(['room_id' => $room->id, 'name' => '4', 'x' => 100, 'y' => 80]);
        $order = app(OrderService::class)->open($table->id, 2);
        $shares = $this->plan($order, 'equal', 2)->shares()->get();
        $this->pay($order, ['cash' => 250, 'card' => 0, 'voucher' => 0], $shares[0]);
        $this->assertDatabaseHas('order_tables', ['order_id' => $order->id, 'active_table_id' => $table->id]);
        $this->pay($order, ['cash' => 0, 'card' => 250, 'voucher' => 0], $shares[1]);
        $this->assertDatabaseHas('order_tables', ['order_id' => $order->id, 'active_table_id' => null]);
    }

    public function test_payment_modal_splits_cash_card_vouchers_and_prefills_fiscal_voucher_count(): void
    {
        $order = $this->order(3000);
        Livewire::test(Show::class, ['order' => $order])->call('openPayment')->assertSet('cashAmount', '30.00')
            ->set('cashAmount', '10,00')->set('voucherAmount', '5.00')->set('voucherIssuer', 'Emittente')->set('voucherQuantity', 2)
            ->call('fillRemaining', 'card')->assertSet('cardAmount', '15.00')->call('checkout')->assertHasNoErrors()->assertSet('paymentModal', false);
        $this->assertSame('paid', $order->fresh()->status);
        Livewire::test(FiscalDocuments::class, ['order' => $order])->assertSet('voucherCount', 2);
    }

    public function test_item_assignment_modal_shows_the_payer_total_and_remaining_products(): void
    {
        [$order, $pizza] = $this->products();
        $plan = $this->plan($order, 'items', 2);
        $share = $plan->shares()->first();
        Livewire::test(Show::class, ['order' => $order])->call('openAssignment', $share->id)->set('shareLabel', 'Mario')
            ->set('selectedQuantities.'.$pizza->id, 1)->set('selectedQuantities.covers', 1)->assertSee('9,90')->assertSee('14,40')
            ->call('saveAssignment')->assertHasNoErrors()->assertSet('assignmentModal', false)->assertSee('Mario');
        $this->assertSame(990, $share->fresh()->amount_cents);
    }

    public function test_a_storage_failure_in_the_second_method_rolls_back_the_whole_mixed_receipt(): void
    {
        $order = $this->order();
        $event = 'eloquent.creating: '.\App\Models\OrderPayment::class;
        \Illuminate\Support\Facades\Event::listen($event, function ($payment) use ($order) {
            if ($payment->order_id === $order->id && $payment->method === 'card') {
                throw new \RuntimeException('Simulated payment storage failure');
            }
        });
        try {
            $this->pay($order, ['cash' => 1000, 'card' => 2000, 'voucher' => 0]);
            $this->fail('Expected failure while saving the second payment method');
        } catch (\RuntimeException $error) {
            $this->assertSame('Simulated payment storage failure', $error->getMessage());
        } finally {
            \Illuminate\Support\Facades\Event::forget($event);
        }
        $this->assertDatabaseCount('order_payments', 0);
        $this->assertDatabaseCount('cash_payment_batches', 0);
        $this->assertNull($order->fresh()->cash_snapshot);
        $this->assertSame('open', $order->fresh()->status);
    }

    public function test_another_orders_share_cannot_be_charged(): void
    {
        $order = $this->order();
        $this->plan($order);
        $foreign = $this->plan($this->order())->shares()->first();
        try {
            $this->pay($order, ['cash' => 1000, 'card' => 0, 'voucher' => 0], $foreign);
            $this->fail('Expected a foreign-share rejection');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            $this->assertDatabaseCount('order_payments', 0);
            $this->assertDatabaseCount('cash_payment_batches', 0);
        }
    }

    public function test_discount_remainders_are_exact_even_when_unit_prices_differ_by_a_cent(): void
    {
        $order = $this->order(0);
        $order->update(['covers' => 0]);
        $course = $order->courses()->create(['number' => 1]);
        $one = $course->items()->create(['product_name' => 'Uno', 'menu_name' => 'Menu', 'quantity' => 3, 'base_price_cents' => 5, 'unit_price_cents' => 5, 'vat' => 10]);
        $two = $course->items()->create(['product_name' => 'Due', 'menu_name' => 'Menu', 'quantity' => 3, 'base_price_cents' => 7, 'unit_price_cents' => 7, 'vat' => 22]);
        app(OrderService::class)->discount($order->id, 1, 'Arrotondamento promozione', 36);
        $plan = $this->plan($order, 'items', 2);
        [$first, $second, $rest] = $plan->shares()->get()->all();
        app(CashBillService::class)->assign($order->id, $first->id, [$one->id => 1, $two->id => 1], 'Uno', $plan->fresh()->version);
        app(CashBillService::class)->assign($order->id, $second->id, [$one->id => 2, $two->id => 1], 'Due', $plan->fresh()->version);
        $this->assertSame([12, 17, 6], $plan->shares()->pluck('amount_cents')->all());
        foreach ([$first, $second, $rest] as $share) {
            $this->pay($order, ['cash' => 0, 'card' => $share->fresh()->amount_cents, 'voucher' => 0], $share);
        }
        $this->assertSame(35, (int) $order->payments()->sum('amount_cents'));
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_a_card_tip_does_not_increase_expected_cash_when_the_bill_is_paid_in_cash(): void
    {
        $shift = app(CashShiftService::class)->open(5000);
        $order = $this->order();
        $this->pay($order, ['cash' => 3000, 'card' => 0, 'voucher' => 0], extra: ['tip' => 200, 'tipMethod' => 'card']);
        $this->assertSame(8000, $shift->expectedCents());
        $this->assertDatabaseHas('order_payments', ['order_id' => $order->id, 'method' => 'card', 'amount_cents' => 0, 'tip_cents' => 200]);
        $this->assertSame(3000, (int) $order->payments()->sum('amount_cents'));
    }

    public function test_customer_paid_notification_is_sent_only_after_the_last_mixed_receipt(): void
    {
        $order = $this->order();
        $order->update(['customer_email' => 'customer@example.test']);
        $notifications = \Mockery::mock(\App\Services\PublicOrders\OrderNotifications::class);
        $notifications->shouldReceive('status')->once()->with(\Mockery::on(fn ($model) => $model->id === $order->id && $model->status === 'paid'), 'paid');
        $this->app->instance(\App\Services\PublicOrders\OrderNotifications::class, $notifications);
        $this->pay($order, ['cash' => 500, 'card' => 500, 'voucher' => 0]);
        $key = (string) Str::uuid();
        $this->pay($order, ['cash' => 0, 'card' => 2000, 'voucher' => 0], key: $key);
        $this->pay($order, ['cash' => 0, 'card' => 2000, 'voucher' => 0], key: $key);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_zero_total_can_still_be_closed_from_the_new_payment_modal(): void
    {
        $order = $this->order();
        app(OrderService::class)->discount($order->id, 10000, 'Omaggio', 3000, true);
        Livewire::test(Show::class, ['order' => $order])->call('openPayment')->assertSet('cashAmount', '0.00')->call('checkout')->assertHasNoErrors();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertDatabaseCount('cash_payment_batches', 1);
    }

    public function test_cash_permission_is_required_for_every_mutation(): void
    {
        $order = $this->order();
        $this->actingAs(User::factory()->create());
        Livewire::test(Show::class, ['order' => $order])->assertForbidden();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->plan($order);
    }
}
