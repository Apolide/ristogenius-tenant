<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FidelityReward extends Model
{
    protected $guarded = [];

    protected $casts = ['points' => 'integer', 'discount_cents' => 'integer'];
}
