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
            return 'Scaduta';
        }

        return ['pending' => 'In attesa del cliente', 'accepted' => 'Accettata dal cliente', 'rejected' => 'Rifiutata dal cliente', 'withdrawn' => 'Ritirata dal ristorante', 'expired' => 'Scaduta'][$this->status] ?? $this->status;
    }
}
