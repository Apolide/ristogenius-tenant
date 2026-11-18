<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FiscalApiCall extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['estimated_cost_micros' => 'integer'];
}
