<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CostHistory extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'costs_history';

    protected $fillable = [
        'id',
        'tenant_id',
        'month',
        'year',
        'cost_details',
        'total_cost',
        'charged',
        'charged_at',
        'payment_intent_id',
        'charges_attempt',
        'last_charge_attempt_at'
    ];

    protected $casts = [
        'cost_details'           => 'array',
        'charged'                => 'boolean',
        'charged_at'             => 'datetime',
        'last_charge_attempt_at' => 'datetime',
    ];

    // Se hai un modello Tenant, puoi abilitarne la relazione:
    // public function tenant()
    // {
    //     return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    // }
}
