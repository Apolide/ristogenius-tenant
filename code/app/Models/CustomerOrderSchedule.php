<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrderSchedule extends Model
{
    protected $guarded = [];

    protected $casts = ['pickup' => 'boolean', 'delivery' => 'boolean'];
}
