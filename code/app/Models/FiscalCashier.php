<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FiscalCashier extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['allow_workstation_switch' => 'boolean'];
}
