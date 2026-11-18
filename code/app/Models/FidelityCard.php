<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FidelityCard extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['points' => 'integer'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function statusUrl(): string
    {
        return route('fidelity.status', ['token' => $this->token]);
    }
}
