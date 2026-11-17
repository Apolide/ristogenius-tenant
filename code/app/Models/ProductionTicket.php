<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ProductionTicket extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime', 'started_at' => 'datetime', 'ready_at' => 'datetime', 'served_at' => 'datetime'];

    public function course()
    {
        return $this->belongsTo(OrderCourse::class, 'order_course_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return ['queued' => __('Da fare'), 'preparing' => __('In preparazione'), 'ready' => __('Pronto'), 'served' => __('Servito'), 'cancelled' => __('Annullato')][$this->status] ?? $this->status;
    }
}
