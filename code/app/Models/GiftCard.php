<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    use HasUuids;

    protected $fillable = [
        'reference', 'code', 'amount_cents', 'language', 'sender_firstname', 'sender_lastname',
        'sender_email', 'sender_phone', 'recipient_firstname', 'recipient_lastname',
        'recipient_phone', 'recipient_email', 'dedication', 'delivery_channel',
        'payment_method', 'paid_at', 'redeemed_at',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'paid_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function formattedAmount(?string $language = null): string
    {
        return \Illuminate\Support\Number::currency($this->amount_cents / 100, 'EUR', $language ?? app()->getLocale());
    }

    public function status(): string
    {
        return match (true) {
            $this->redeemed_at !== null => 'redeemed',
            $this->paid_at !== null => 'available',
            default => 'pending',
        };
    }
}
