<?php

namespace Tests\Feature\Orders;

use App\Livewire\CashDesk\Shifts;
use App\Livewire\CashDesk\Show;
use App\Models\Order;
use App\Models\User;
use App\Services\Orders\CashShiftService;
use App\Services\Orders\CashTotalsService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CashEngineTest extends TestCase
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

    private function order(int $cents = 1000): Order
    {
        return Order::create(['service_date' => today(), 'fulfillment' => 'pickup', 'fulfillment_status' => 'accepted', 'covers' => 1, 'cover_price_cents' => $cents]);
    }

    public function test_partial_payments_mixed_methods_tips_and_idempotency(): void
    {
        $order = $this->order();
        $service = app(OrderService::class);
        $key = (string) Str::uuid();
        $service->pay($order->id, 'cash', 1000, 400, 50, $key, 'Mario');
        $service->pay($order->id, 'cash', 1000, 400, 50, $key, 'Mario');
        $this->assertSame('open', $order->fresh()->status);
        $this->assertSame(600, $order->fresh()->balanceCents());
        $this->assertCount(1, $order->payments()->get());
        $service->pay($order->id, 'card', 1000, 600, 100, (string) Str::uuid());
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(0, $order->fresh()->balanceCents());
        $this->assertSame(150, (int) $order->payments()->sum('tip_cents'));
    }

    public function test_idempotency_key_cannot_be_reused_with_changed_payload(): void
    {
        $order = $this->order();
        $key = (string) Str::uuid();
        app(OrderService::class)->pay($order->id, 'cash', 1000, 100, key: $key);
        $this->expectException(ValidationException::class);
        app(OrderService::class)->pay($order->id, 'cash', 1000, 200, key: $key);
    }

    public function test_overpayment_is_rejected(): void
    {
        $order = $this->order();
        app(OrderService::class)->pay($order->id, 'cash', 1000, 600);
        $this->expectException(ValidationException::class);
        app(OrderService::class)->pay($order->id, 'card', 1000, 500);
    }

    public function test_stale_balance_cannot_charge_an_unseen_residual(): void
    {
        $order = $this->order();
        app(OrderService::class)->pay($order->id, 'cash', 1000, 100);
        $this->expectException(ValidationException::class);
        app(OrderService::class)->pay($order->id, 'card', 1000, expectedBalance: 1000);
    }

    public function test_partial_payment_blocks_discounts_cancellation_and_item_changes(): void
    {
        $order = $this->order();
        app(OrderService::class)->pay($order->id, 'cash', 1000, 100);
        foreach (['discount', 'cancel', 'covers'] as $action) {
            try {
                match ($action) {
                    'discount' => app(OrderService::class)->discount($order->id, 10, 'Sconto', 1000),
                    'cancel' => app(OrderService::class)->cancel($order->id),
                    'covers' => app(OrderService::class)->covers($order->id, 2),
                };
                $this->fail('Expected frozen account rejection');
            } catch (ValidationException) {
                $this->assertSame('open', $order->fresh()->status);
            }
        }
    }

    public function test_discount_and_mixed_vat_reconcile_to_the_cent(): void
    {
        $order = $this->order(1);
        $course = $order->courses()->create(['number' => 1]);
        foreach ([10 => 999, 22 => 1001] as $vat => $price) {
            $course->items()->create(['product_name' => 'Prodotto', 'menu_name' => 'Menu', 'quantity' => 1,
                'base_price_cents' => $price, 'unit_price_cents' => $price, 'vat' => $vat]);
        }
        app(OrderService::class)->discount($order->id, 3333, 'Sconto percentuale', 2001, true);
        $snapshot = app(CashTotalsService::class)->snapshot($order->fresh());
        $this->assertSame(667, $snapshot['discount_cents']);
        $this->assertSame(1334, $snapshot['total_cents']);
        $this->assertSame(1334, array_sum(array_column($snapshot['taxes'], 'gross_cents')));
        foreach ($snapshot['taxes'] as $tax) {
            $this->assertSame($tax['gross_cents'], $tax['net_cents'] + $tax['vat_cents']);
        }
        app(OrderService::class)->pay($order->id, 'cash', 1334, 100);
        $order->update(['cover_price_cents' => 50000]);
        $this->assertSame(1334, $order->fresh()->totalCents());
        $this->assertEquals($snapshot, app(CashTotalsService::class)->snapshot($order->fresh()));
    }

    public function test_discount_is_capped_when_an_unpaid_account_is_reduced(): void
    {
        $order = $this->order();
        app(OrderService::class)->discount($order->id, 800, 'Promozione', 1000);
        // Reducing covers/items before the first payment can reduce the gross total.
        $order->update(['cover_price_cents' => 500]);
        $order->refresh();
        $this->assertSame(500, $order->effectiveDiscountCents());
        $this->assertSame(0, $order->totalCents());
        $summary = app(\App\Services\PublicOrders\OrderSummaryService::class)->snapshot($order);
        $this->assertSame(500, $summary['discount_cents']);
        app(OrderService::class)->pay($order->id, 'manual', 0);
        $this->assertSame(500, $order->fresh()->effectiveDiscountCents());
    }

    public function test_vouchers_require_issuer_and_cannot_pay_tips(): void
    {
        $order = $this->order();
        Livewire::test(Show::class, ['order' => $order])->set('method', 'voucher')->set('amount', '3,50')->call('pay')->assertHasErrors('order');
        Livewire::test(Show::class, ['order' => $order])->set('method', 'voucher')->set('voucherIssuer', 'Emittente')->set('tip', '1')->call('pay')->assertHasErrors('order');
        Livewire::test(Show::class, ['order' => $order])->set('method', 'voucher')->set('voucherIssuer', 'Emittente')->set('amount', '3,50')->call('pay')->assertHasNoErrors();
        $this->assertSame(650, $order->fresh()->balanceCents());
    }

    public function test_shift_reconciles_cash_and_tips_excludes_other_methods_and_is_immutable(): void
    {
        $service = app(CashShiftService::class);
        $shift = $service->open(5000);
        $order = $this->order();
        app(OrderService::class)->pay($order->id, 'cash', 1000, 400, 50);
        app(OrderService::class)->pay($order->id, 'card', 1000, 600, 100);
        $this->assertSame(5450, $shift->expectedCents());
        $service->close($shift->id, 5400, 'Differenza verificata');
        $this->assertSame(-50, (int) $shift->fresh()->difference_cents);
        $service->close($shift->id, 0);
        $this->assertSame(5400, (int) $shift->fresh()->counted_cents);
        $new = $service->open(5400);
        $this->assertNotSame($shift->id, $new->id);
        app(OrderService::class)->pay($this->order()->id, 'cash', 1000);
        $this->assertSame(6400, $new->expectedCents());
        $this->assertSame(5450, $shift->fresh()->expectedCents());
    }

    public function test_only_one_shift_can_be_open(): void
    {
        app(CashShiftService::class)->open(0);
        $this->expectException(ValidationException::class);
        app(CashShiftService::class)->open(0);
    }

    public function test_split_ui_handles_final_rounding_and_free_account(): void
    {
        $order = $this->order(1001);
        Livewire::test(Show::class, ['order' => $order])->set('shares', 3)->call('split')->assertHasNoErrors();
        $shares = $order->billPlan()->firstOrFail()->shares()->get();
        $this->assertSame([334, 334, 333], $shares->pluck('amount_cents')->all());
        foreach ($shares as $share) {
            Livewire::test(Show::class, ['order' => $order])->call('openPayment', $share->id)->call('checkout')->assertHasNoErrors();
        }
        $this->assertSame('paid', $order->fresh()->status);
        $free = $this->order();
        app(OrderService::class)->discount($free->id, 10000, 'Omaggio', 1000, true);
        app(OrderService::class)->pay($free->id, 'manual', 0);
        $this->assertSame('paid', $free->fresh()->status);
    }

    public function test_cash_pages_and_shift_actions_require_cash_permission(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(Show::class, ['order' => $this->order()])->assertForbidden();
        Livewire::test(Shifts::class)->assertForbidden();
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(CashShiftService::class)->open(0);
    }

    public function test_shift_ui_opens_and_closes(): void
    {
        Livewire::test(Shifts::class)->set('opening', '50,00')->call('open')->assertHasNoErrors()->assertSee('50,00');
        $shift = \App\Models\CashShift::whereNotNull('active_key')->firstOrFail();
        Livewire::test(Shifts::class)->set('counted', '49.00')->call('close', $shift->id)->assertHasNoErrors()->assertSee('-1,00');
    }
}
