<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSettingException extends Model
{
    public const TYPE_OPENING_HOURS = 'opening_hours';
    public const TYPE_PAX_CAPACITY = 'pax_capacity';

    protected $fillable = [
        'type',
        'starts_on',
        'ends_on',
        'payload',
    ];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'payload' => 'array',
    ];
}
