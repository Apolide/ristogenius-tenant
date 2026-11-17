<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CustomerOrderProposal extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $hidden = ['token_hash'];

    protected $casts = ['payload' => 'array', 'expires_at' => 'datetime', 'responded_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function statusLabel(): string
    {
        if ($this->status === 'pending' && $this->expires_at->isPast()) {
            return __('Scaduta');
        }

        return ['pending' => __('In attesa del cliente'), 'accepted' => __('Accettata dal cliente'), 'rejected' => __('Rifiutata dal cliente'), 'withdrawn' => __('Ritirata dal ristorante'), 'expired' => __('Scaduta')][$this->status] ?? $this->status;
    }
}
