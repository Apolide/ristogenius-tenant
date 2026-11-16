<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderAccess extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['identity' => 'encrypted:array', 'expires_at' => 'datetime', 'consumed_at' => 'datetime'];
}
