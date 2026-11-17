<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollInvitation extends Model
{
    use HasUuids;

    protected $fillable = ['poll_id', 'booking_id', 'language', 'snapshot', 'answers', 'rating', 'submitted_at'];

    protected $casts = ['snapshot' => 'array', 'answers' => 'array', 'submitted_at' => 'datetime', 'rating' => 'decimal:2'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }
}
