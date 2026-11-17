<?php

namespace App\Services\Orders;

use App\Models\CashBillPlan;
use App\Models\CashPaymentBatch;
use App\Models\Order;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CashBillService
{
    public function __construct(private CashBillPricingService $pricing) {}

    public function create(string $orderId, string $mode, int $count, int $expectedTotal, int $expectedBalance): CashBillPlan
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('mode', 'count'), ['mode' => 'in:equal,items', 'count' => 'integer|min:2|max:100'])->validate();

        return DB::transaction(function () use ($orderId, $mode, $count, $expectedTotal, $expectedBalance) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->check($order->status === 'open' && ! $order->pendingProposal()->exists(), __('Conto non disponibile per la divisione.'));
            $this->check($order->totalCents() === $expectedTotal && $order->balanceCents() === $expectedBalance, __('Il conto è cambiato. Verifica il residuo prima di dividerlo.'));
            $this->check($expectedBalance > 0, __('Nessun residuo da dividere.'));
            $this->check($mode !== 'items' || ! $order->payments()->exists(), __('La divisione per prodotti va preparata prima del primo incasso. Puoi ancora dividere il residuo per importo.'));
            $plan = $order->billPlan()->first();
            if ($plan) {
                $this->check(! CashPaymentBatch::whereIn('cash_bill_share_id', $plan->shares()->select('id'))->exists(), __('Una quota è già stata incassata: la divisione non può essere sostituita.'));
            }
            $pricing = $this->pricing->snapshot($order);
            $values = ['mode' => $mode, 'version' => ($plan?->version ?? 0) + 1, 'pricing' => $pricing, 'pricing_hash' => $this->pricing->hash($pricing),
                'previous_paid_cents' => (int) $order->payments()->sum('amount_cents')];
            if ($plan) {
                $plan->shares()->delete();
                $plan->update($values);
            } else {
                $plan = $order->billPlan()->create($values);
            }
            $this->check($mode !== 'equal' || $expectedBalance >= $count, __('Il residuo è troppo piccolo per il numero di quote.'));
            for ($i = 0; $i < $count; $i++) {
                $plan->shares()->create(['position' => $i + 1, 'label' => 'Pagante '.($i + 1), 'units' => [],
                    'amount_cents' => $mode === 'equal' ? intdiv($expectedBalance, $count) + ($i < $expectedBalance % $count ? 1 : 0) : 0]);
            }
            if ($mode === 'items') {
                $plan->shares()->create(['position' => $count + 1, 'label' => 'Rimanenza / ultimo pagante', 'is_remainder' => true,
                    'amount_cents' => $expectedBalance, 'units' => array_column($pricing['units'], 'id')]);
            }
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'bill_split_created', 'details' => ['mode' => $mode, 'quantity' => $count]]);

            return $plan->fresh('shares');
        });
    }

    public function assertCurrent(Order $order, CashBillPlan $plan, ?int $version): void
    {
        $this->check($version !== null && $version === $plan->version, __('La divisione è cambiata. Riapri la quota prima di confermare.'));
        $this->check(hash_equals($plan->pricing_hash, $this->pricing->hash($this->pricing->snapshot($order))), __('Il conto è stato modificato dopo la divisione. Ricrea le quote prima di incassare.'));
    }

    public function current(Order $order, CashBillPlan $plan): bool
    {
        return hash_equals($plan->pricing_hash, $this->pricing->hash($this->pricing->snapshot($order)));
    }

    public function assign(string $orderId, string $shareId, array $quantities, string $label, int $version): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('quantities', 'label'), ['quantities' => 'array|max:1000', 'quantities.*' => 'integer|min:0|max:10000', 'label' => 'required|string|max:100'])->validate();
        DB::transaction(function () use ($orderId, $shareId, $quantities, $label, $version) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->check($order->status === 'open', __('Il conto è chiuso.'));
            $plan = $order->billPlan()->firstOrFail();
            $this->assertCurrent($order, $plan, $version);
            $this->check($plan->mode === 'items', __('Questa divisione non è per prodotti.'));
            $share = $plan->shares()->findOrFail($shareId);
            $this->check(! $share->is_remainder && ! $share->batches()->exists(), __('Questa quota non può essere modificata.'));
            $remainder = $plan->shares()->where('is_remainder', true)->firstOrFail();
            $this->check(! $remainder->batches()->exists(), __('La rimanenza è già stata incassata: non puoi cambiare le assegnazioni.'));
            $others = $plan->shares()->where('id', '!=', $share->id)->where('is_remainder', false)->get()->flatMap(fn ($row) => $row->units)->all();
            $catalog = collect($plan->pricing['units']);
            $available = $catalog->whereNotIn('id', $others)->groupBy('line_id');
            $known = $catalog->pluck('line_id')->unique()->all();
            $chosen = [];
            foreach ($quantities as $line => $quantity) {
                $this->check(in_array($line, $known, true), __('Prodotto non appartenente al conto.'));
                $pool = $available->get($line, collect());
                $this->check($quantity <= $pool->count(), __('Quantità non disponibile: alcuni prodotti sono già assegnati ad altri paganti.'));
                $chosen = array_merge($chosen, $pool->take($quantity)->pluck('id')->all());
            }
            $share->update(['label' => trim($label) === $share->displayLabel() ? $share->label : trim($label), 'units' => $chosen, 'amount_cents' => (int) $catalog->whereIn('id', $chosen)->sum('amount_cents')]);
            $unassigned = $catalog->whereNotIn('id', array_merge($others, $chosen));
            $remainder->update(['units' => $unassigned->pluck('id')->values()->all(), 'amount_cents' => (int) $unassigned->sum('amount_cents')]);
            $plan->increment('version');
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'bill_items_assigned', 'details' => ['payer' => $share->label, 'amount_cents' => $share->amount_cents]]);
        });
    }

    public function remove(string $orderId, int $version): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        DB::transaction(function () use ($orderId, $version) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $plan = $order->billPlan()->firstOrFail();
            $this->check($order->status === 'open' && $plan->version === $version, __('La divisione è cambiata o il conto è chiuso.'));
            $this->check(! CashPaymentBatch::whereIn('cash_bill_share_id', $plan->shares()->select('id'))->exists(), __('Una quota è già stata incassata.'));
            $plan->delete();
        });
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}
