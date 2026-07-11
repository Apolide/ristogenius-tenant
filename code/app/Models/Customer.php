<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'firstname',
        'lastname',
        'display_name',
        'email',
        'phone',
        'region_id',
        'province_id',
        'comuni_id',
        'registration_source',
        'telegramid',
        'birthdate',
        'consent_privacy',
        'consent_marketing',
        'datetime_consent_privacy',
        'datetime_consent_marketing',
        'ip_consent_privacy',
        'ip_consent_marketing',
        'note',
        'lang',
        'last_action_at',
        'blacklisted',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'consent_privacy' => 'boolean',
        'consent_marketing' => 'boolean',
        'datetime_consent_privacy' => 'datetime',
        'datetime_consent_marketing' => 'datetime',
        'last_action_at' => 'datetime',
        'blacklisted' => 'boolean',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function comune(): BelongsTo
    {
        return $this->belongsTo(Comuni::class, 'comuni_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
