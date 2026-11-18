<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponUsage extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'consumed_at' => 'datetime'];

    public function assignment()
    {
        return $this->belongsTo(CouponAssignment::class, 'coupon_assignment_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
