<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderProposal extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $hidden = ['token_hash'];

    protected $casts = ['payload' => 'array', 'expires_at' => 'datetime', 'responded_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function statusLabel(): string
    {
        if ($this->status === 'pending' && $this->expires_at->isPast()) {
            return __('customer_orders.messages.expired');
        }

        return ['pending' => __('customer_orders.messages.waiting_for_customer'), 'accepted' => __('customer_orders.messages.accepted_by_customer'), 'rejected' => __('customer_orders.messages.rejected_by_customer'), 'withdrawn' => __('customer_orders.messages.withdrawn_by_restaurant'), 'expired' => __('customer_orders.messages.expired')][$this->status] ?? $this->status;
    }
}
