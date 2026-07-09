<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'production',
        'use_printer',
        'printer_number',
    ];

    protected $casts = [
        'production' => 'boolean',
        'use_printer' => 'boolean',
        'printer_number' => 'integer',
    ];
}
