<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashPaymentBatch extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['amount_cents' => 'integer', 'tip_cents' => 'integer', 'fiscal_snapshot' => 'array'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }
}
