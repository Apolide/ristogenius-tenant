<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalProfile extends Model
{
    protected $guarded = [];

    protected $casts = ['address' => 'array', 'enabled' => 'boolean', 'synced_at' => 'datetime'];
}
