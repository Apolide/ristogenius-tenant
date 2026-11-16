<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['service_date' => 'date', 'paid_at' => 'datetime', 'cancelled_at' => 'datetime'];

    public function courses()
    {
        return $this->hasMany(OrderCourse::class)->orderBy('number');
    }

    public function tables()
    {
        return $this->belongsToMany(RoomTable::class, 'order_tables');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class)->latest('id');
    }

    public function items()
    {
        return $this->hasManyThrough(OrderItem::class, OrderCourse::class);
    }

    public function totalCents(): int
    {
        return $this->covers * $this->cover_price_cents + (int) $this->items()->selectRaw('COALESCE(SUM(quantity * unit_price_cents), 0) as total')->value('total');
    }
}
