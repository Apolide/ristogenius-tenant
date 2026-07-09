<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'settings_rooms';

    protected $fillable = [
        'name',
        'active',
        'service_charge',
        'service_charge_percentage',
        'order',
        'capacity',
        'smoking_allowed',
    ];

    protected $casts = [
        'active' => 'boolean',
        'service_charge' => 'decimal:2',
        'service_charge_percentage' => 'decimal:2',
        'order' => 'integer',
        'capacity' => 'integer',
        'smoking_allowed' => 'boolean',
    ];

    public function tables(): HasMany
    {
        return $this->hasMany(RoomTable::class);
    }
}
