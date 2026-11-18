<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponClaim extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['identity' => 'encrypted:array', 'expires_at' => 'datetime'];

    protected $hidden = ['identity', 'token_hash'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
