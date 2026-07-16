<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingHistory extends Model
{
    protected $fillable = ['booking_id', 'event', 'actor', 'description', 'changes'];
    protected $casts = ['changes' => 'array'];
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class)->withTrashed(); }
}
