<?php

namespace App\Services\Fidelity;

use App\Models\CashRefund;
use App\Models\Customer;
use App\Models\FidelityCard;
use App\Models\FidelityMovement;
use App\Models\FidelityProgram;
use App\Models\FidelityReward;
use App\Models\Order;
use App\Services\Marketing\CouponPricing;
use App\Services\Orders\CashTotalsService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FidelityCheckout
{
    public function card(Order $order): ?FidelityCard
    {
        if ($order->fidelity_reward) {
            return FidelityCard::find($order->fidelity_reward['card']);
        }

        if ($order->fidelity_card_id) {
            return FidelityCard::find($order->fidelity_card_id);
        }

        $customerId = $order->customer_id ?? $order->booking?->customer_id;
        if (! $customerId && $order->fulfillment === 'dine_in') {
            $customers = $order->courses()->whereNull('rejected_at')->whereNotNull('customer_id')
                ->whereHas('items')->reorder()->distinct()->limit(2)->pluck('customer_id');
            $customerId = $customers->count() === 1 ? $customers->first() : null;
        }

        return $customerId ? FidelityCard::where('customer_id', $customerId)->first() : null;
    }

    public function associate(string $orderId, ?string $customerId, int $expectedTotal): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        DB::transaction(function () use ($orderId, $customerId, $expectedTotal) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->assertEditable($order, $expectedTotal);
            $this->check(! $order->fidelity_reward, 'cannot_associate');
            $this->check(! $customerId || FidelityProgram::findOrFail(1)->active, 'inactive');
            $card = $customerId ? Customer::findOrFail($customerId)->fidelityCard()->firstOrFail() : null;
            $order->update(['fidelity_card_id' => $card?->id]);
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'fidelity_customer_associated', 'details' => ['customer_id' => $customerId]]);
        });
    }

    public function availableRewards(Order $order, FidelityCard $card): Collection
    {
        if ($order->fidelity_reward || $order->effectiveDiscountCents() || $order->discount_cents) {
            return collect();
        }

        $lines = collect(app(CashTotalsService::class)->lines($order))->where('unit_price_cents', '>', 0);
        $discountLimit = $lines->where('id', '!=', 'delivery')->sum('gross_cents');
        $products = $lines->pluck('product_id')->filter()->unique();

        return FidelityReward::where('points', '<=', $card->points)
            ->where(fn ($query) => $query->where('discount_cents', '<=', $discountLimit)->orWhereIn('product_id', $products))
            ->orderBy('points')->orderBy('id')->get();
    }

    public function reserve(string $orderId, int $rewardId, int $expectedTotal): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        DB::transaction(function () use ($orderId, $rewardId, $expectedTotal) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->assertEditable($order, $expectedTotal);
            $this->check(! $order->fidelity_reward && $order->effectiveDiscountCents() === 0 && ! $order->discount_cents, 'existing_discount');
            $this->check(FidelityProgram::findOrFail(1)->active, 'inactive');
            $reward = FidelityReward::findOrFail($rewardId);
            $cardId = $this->card($order)?->id;
            $card = $cardId ? FidelityCard::lockForUpdate()->find($cardId) : null;
            $this->check($card && $card->points >= $reward->points, 'insufficient');
            $selection = $reward->only('name', 'points', 'product_id', 'discount_cents');
            $selection['card'] = $card->id;
            $selection['reservation'] = \Illuminate\Support\Str::uuid()->toString();
            $order->fidelity_reward = $selection;
            $this->assertReward($order);

            $this->move($card, 'reserve:'.$selection['reservation'], -$reward->points);
            $order->save();
        }, 3);
    }

    public function remove(string $orderId, int $expectedTotal): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        DB::transaction(function () use ($orderId, $expectedTotal) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->assertEditable($order, $expectedTotal);
            $this->release($order);
        }, 3);
    }

    public function release(Order $order): void
    {
        if (! $selection = $order->fidelity_reward) {
            return;
        }

        $card = FidelityCard::lockForUpdate()->find($selection['card']);
        if ($card) {
            $this->move($card, 'release:'.$selection['reservation'], $selection['points']);
        }
        $order->update(['fidelity_reward' => null]);
    }

    public function discounts(Order $order, array $lines): array
    {
        if (! $selection = $order->fidelity_reward) {
            return [];
        }

        if ($selection['product_id']) {
            foreach ($lines as $line) {
                if ($line['product_id'] === $selection['product_id'] && $line['unit_price_cents'] > 0) {
                    return [$line['id'] => $line['unit_price_cents']];
                }
            }

            return [];
        }

        $weights = [];
        foreach ($lines as $line) {
            if ($line['id'] !== 'delivery' && $line['gross_cents'] > 0) {
                $weights[$line['id']] = $line['gross_cents'];
            }
        }

        return app(CouponPricing::class)->allocate(min($selection['discount_cents'], array_sum($weights)), $weights);
    }

    public function assertReward(Order $order): void
    {
        if (! $selection = $order->fidelity_reward) {
            return;
        }
        if ($order->cash_snapshot !== null) {
            return;
        }

        $lines = app(CashTotalsService::class)->lines($order);
        $discount = array_sum($this->discounts($order, $lines));
        $this->check(FidelityCard::whereKey($selection['card'])->exists(), 'no_card');
        $this->check(! $order->discount_cents && ! array_sum(app(CouponPricing::class)->discounts($order, $lines)), 'existing_discount');
        $this->check($discount > 0 && (! $selection['discount_cents'] || $selection['discount_cents'] === $discount), 'reward_unavailable');
    }

    public function preview(Order $order, FidelityProgram $program): int
    {
        $rate = $this->rate($order, $program);

        return $rate ? $this->points($this->eligibleCents($order), $order->totalCents(), $this->refundedCents($order), $rate) : 0;
    }

    public function settle(Order $order): void
    {
        $program = FidelityProgram::findOrFail(1);
        if (! $program->active && ! $order->fidelity_reward) {
            return;
        }
        $cardId = $this->card($order)?->id;
        if (! $cardId || ! $card = FidelityCard::lockForUpdate()->find($cardId)) {
            return;
        }

        $eligible = $this->eligibleCents($order);
        $total = $order->totalCents();
        $rate = $this->rate($order, $program);
        $earned = $this->points($eligible, $total, $this->refundedCents($order), $rate);
        $reference = 'paid:'.$order->id;
        $created = $this->move($card, $reference, $earned, compact('eligible', 'total', 'rate'));
        if ($created && ($earned > 0 || $order->fidelity_reward)) {
            app(FidelityMail::class)->notify($card, $program, 'balance', $reference);
        }
    }

    public function refund(Order $order, CashRefund $refund): void
    {
        $settlement = FidelityMovement::where('reference', 'paid:'.$order->id)->first();
        if (! $settlement || ! $card = FidelityCard::lockForUpdate()->find($settlement->fidelity_card_id)) {
            return;
        }
        ['eligible' => $eligible, 'total' => $total, 'rate' => $rate] = $settlement->details;
        $refunded = $this->refundedCents($order);
        $earned = $this->points($eligible, $total, $refunded, $rate);
        $previous = $settlement->points + (int) FidelityMovement::where('reference', 'like', 'refund:'.$order->id.':%')->sum('points');
        $reference = 'refund:'.$order->id.':'.$refund->id;
        $changed = $previous !== $earned && $this->move($card, $reference, $earned - $previous);

        if ($total > 0 && $refunded >= $total && $order->fidelity_reward) {
            $changed = $this->move($card, 'restore:'.$order->id, $order->fidelity_reward['points']) || $changed;
        }
        if ($changed) {
            app(FidelityMail::class)->notify($card, FidelityProgram::findOrFail(1), 'balance', $reference);
        }
    }

    private function assertEditable(Order $order, int $expectedTotal): void
    {
        $this->check($order->status === 'open' && ! $order->cash_snapshot && ! $order->payments()->exists()
            && ! $order->pendingProposal()->exists() && $order->totalCents() === $expectedTotal, 'order_changed');
    }

    private function eligibleCents(Order $order): int
    {
        $snapshot = app(CashTotalsService::class)->snapshot($order);
        $delivery = (int) $order->delivery_fee_cents - ($snapshot['line_discounts']['delivery'] ?? 0);

        return max(0, $snapshot['total_cents'] - $delivery);
    }

    private function rate(Order $order, FidelityProgram $program): int
    {
        return $program->active && in_array($order->fulfillment, $program->channels, true) ? $program->rate : 0;
    }

    private function points(int $eligible, int $total, int $refunded, int $rate): int
    {
        if ($total === 0 || $rate === 0) {
            return 0;
        }

        $remaining = max(0, $total - $refunded);
        // Cash refunds record a single amount. Prorate it across eligible spend and delivery.
        $eligible = intdiv($eligible * $remaining, $total);

        return intdiv($eligible * $rate, 10000);
    }

    private function refundedCents(Order $order): int
    {
        return (int) DB::table('cash_payment_batches as batch')
            ->join('cash_refunds as refund', 'refund.cash_payment_batch_id', '=', 'batch.id')
            ->where('batch.order_id', $order->id)->groupBy('batch.id', 'batch.amount_cents')
            ->selectRaw('batch.amount_cents, sum(refund.amount_cents) as refunded')->get()
            ->sum(fn ($batch) => min((int) $batch->amount_cents, (int) $batch->refunded));
    }

    private function move(FidelityCard $card, string $reference, int $points, ?array $details = null): bool
    {
        $movement = FidelityMovement::firstOrCreate(['reference' => $reference], [
            'fidelity_card_id' => $card->id, 'points' => $points, 'details' => $details,
        ]);
        if ($movement->wasRecentlyCreated && $points !== 0) {
            $card->increment('points', $points);
        }

        return $movement->wasRecentlyCreated;
    }

    private function check(bool $condition, string $key): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => __('fidelity.'.$key)]);
        }
    }
}
