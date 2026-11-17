<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FidelityMovement extends Model
{
    protected $guarded = [];

    protected $casts = ['points' => 'integer', 'details' => 'array'];
}
