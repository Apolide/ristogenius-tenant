<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['service_date' => 'date', 'paid_at' => 'datetime', 'cancelled_at' => 'datetime', 'requested_at' => 'datetime', 'cash_snapshot' => 'array', 'fidelity_reward' => 'array'];

    public function proposals()
    {
        return $this->hasMany(CustomerOrderProposal::class)->latest();
    }

    public function pendingProposal()
    {
        return $this->hasOne(CustomerOrderProposal::class)->where('status', 'pending')->where('expires_at', '>', now());
    }

    public function courses()
    {
        return $this->hasMany(OrderCourse::class)->orderBy('number');
    }

    public function tables()
    {
        return $this->belongsToMany(RoomTable::class, 'order_tables');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function billPlan()
    {
        return $this->hasOne(CashBillPlan::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class)->latest('id');
    }

    public function items()
    {
        return $this->hasManyThrough(OrderItem::class, OrderCourse::class);
    }

    public function serviceFeeCents(): int
    {
        $totals = \Illuminate\Support\Facades\DB::table('order_courses')
            ->join('order_items', 'order_items.order_course_id', '=', 'order_courses.id')
            ->where('order_courses.order_id', $this->id)->whereNull('order_courses.rejected_at')
            ->where('order_courses.service_percentage_bps', '>', 0)
            ->groupBy('order_courses.id', 'order_courses.service_percentage_bps')
            ->selectRaw('order_courses.service_percentage_bps, SUM(order_items.quantity * order_items.unit_price_cents) AS subtotal')->get();

        return (int) $totals->sum(fn ($row) => intdiv((int) $row->subtotal * $row->service_percentage_bps + 5000, 10000));
    }

    public function fulfillmentLabel(?string $locale = null): string
    {
        return ['dine_in' => __('cash_desk.dining_room', [], $locale), 'pickup' => __('cash_desk.history.pickup', [], $locale), 'delivery' => __('cash_desk.delivery', [], $locale)][$this->fulfillment] ?? $this->fulfillment;
    }

    public function fulfillmentStatusLabel(?string $locale = null): string
    {
        if ($this->fulfillment !== 'dine_in' && $this->pendingProposal) {
            return __('orders.waiting_for_customer_response', [], $locale);
        }

        return ['received' => __('orders.awaiting_approval', [], $locale), 'accepted' => __('orders.accepted', [], $locale), 'preparing' => __('cash_desk.bill.preparing', [], $locale), 'ready' => __('cash_desk.bill.ready', [], $locale), 'out_for_delivery' => __('orders.out_for_delivery', [], $locale), 'completed' => __('orders.completed', [], $locale), 'rejected' => __('cash_desk.fiscal_documents.rejected', [], $locale), 'cancelled' => __('cash_desk.cancelled', [], $locale)][$this->fulfillment_status] ?? '';
    }

    public function effectiveDiscountCents(): int
    {
        return $this->cash_snapshot !== null
            ? (int) $this->cash_snapshot['discount_cents']
            : (int) app(\App\Services\Orders\CashTotalsService::class)->snapshot($this)['discount_cents'];
    }

    public function balanceCents(): int
    {
        return max(0, $this->totalCents() - (int) $this->payments()->sum('amount_cents'));
    }

    public function totalCents(): int
    {
        if ($this->cash_snapshot !== null) {
            return (int) $this->cash_snapshot['total_cents'];
        }

        return (int) app(\App\Services\Orders\CashTotalsService::class)->snapshot($this)['total_cents'];
    }

    public function grossCents(): int
    {
        return $this->covers * $this->cover_price_cents + (int) $this->delivery_fee_cents
            + (int) $this->items()->whereNull('order_courses.rejected_at')->selectRaw('COALESCE(SUM(quantity * unit_price_cents), 0) as total')->value('total')
            + $this->serviceFeeCents();
    }
}
