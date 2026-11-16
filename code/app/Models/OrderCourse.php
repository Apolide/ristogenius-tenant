<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderCourse extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['sent_at' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets()
    {
        return $this->hasMany(ProductionTicket::class);
    }
}
