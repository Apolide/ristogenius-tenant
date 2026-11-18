<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FiscalCopyRequest extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['email' => 'encrypted', 'agreed_at' => 'datetime', 'queued_at' => 'datetime'];
}
