<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FiscalOperation extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['request' => 'encrypted:array', 'response' => 'encrypted:array'];

    protected $hidden = ['request', 'response'];
}
