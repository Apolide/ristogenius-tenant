<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingWaitlistEntry extends Model
{
    use HasUuids;

    protected $fillable = [
        'customer_id', 'waitlist_date', 'pax', 'status', 'note',
        'notified_channels', 'notified_at', 'seated_at',
    ];

    protected $casts = [
        'waitlist_date' => 'date',
        'pax' => 'integer',
        'notified_channels' => 'array',
        'notified_at' => 'datetime',
        'seated_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
