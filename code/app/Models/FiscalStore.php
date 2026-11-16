<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FiscalStore extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['address' => 'array', 'registers' => 'array', 'active' => 'boolean', 'last_closed_at' => 'datetime', 'synced_at' => 'datetime'];
}
