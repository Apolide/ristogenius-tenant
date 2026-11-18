<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashBillShare extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['units' => 'array', 'is_remainder' => 'boolean', 'amount_cents' => 'integer'];

    public function displayLabel(): string
    {
        if ($this->is_remainder && $this->label === 'Rimanenza / ultimo pagante') {
            return __('cash_desk.remainder_last_payer');
        }
        if (preg_match('/^Pagante ([0-9]+)$/', $this->label, $match)) {
            return __('cash_desk.payer_number', ['number' => $match[1]]);
        }

        return $this->label;
    }

    public function batches()
    {
        return $this->hasMany(CashPaymentBatch::class);
    }

    public function paidCents(): int
    {
        return array_key_exists('paid_cents', $this->getAttributes()) ? (int) $this->getAttribute('paid_cents') : (int) $this->batches()->sum('amount_cents');
    }

    public function balanceCents(): int
    {
        return max(0, $this->amount_cents - $this->paidCents());
    }
}
