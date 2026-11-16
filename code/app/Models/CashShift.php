<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['closed_at' => 'datetime'];

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function expectedCents(): int
    {
        return (int) $this->opening_cents + (int) $this->payments()->where('method', 'cash')->sum('amount_cents')
            + (int) $this->payments()->where('method', 'cash')->sum('tip_cents');
    }
}
