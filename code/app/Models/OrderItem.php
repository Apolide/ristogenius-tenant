<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['requires_production' => 'boolean'];

    public function course()
    {
        return $this->belongsTo(OrderCourse::class, 'order_course_id');
    }

    public function modifiers()
    {
        return $this->hasMany(OrderItemModifier::class);
    }

    public function ticket()
    {
        return $this->belongsTo(ProductionTicket::class, 'production_ticket_id');
    }

    public function statusLabel(): string
    {
        if ($this->course->rejected_at) {
            return 'Rifiutato';
        }

        return $this->ticket ? $this->ticket->statusLabel() : ($this->course->sent_at ? 'Inviato' : 'In attesa');
    }
}
