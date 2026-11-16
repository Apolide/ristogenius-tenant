<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FiscalDocument extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'remote_data' => 'array', 'document_date' => 'datetime', 'total_cents' => 'integer'];
}
