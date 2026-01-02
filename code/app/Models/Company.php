<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    /**
     * Attributi assegnabili in massa.
     */
    protected $fillable = [
        'external_id',
        'fiscal_code',
        'company_name',
        'activity_status',
        'last_update_timestamp',
        'enrollment_date',
        'address',
        'toponym',
        'street',
        'house_number',
        'municipality',
        'hamlet',
        'province',
        'postal_code',
        'latitude',
        'longitude',
        'recipient_code'
    ];

    /**
     * Cast automatici per i tipi complessi.
     */
    protected $casts = [
        'last_update_timestamp' => 'datetime',
        'enrollment_date'       => 'date',
        'ceased'                => 'date',
        'latitude'              => 'decimal:7',
        'longitude'             => 'decimal:7',
    ];
}
