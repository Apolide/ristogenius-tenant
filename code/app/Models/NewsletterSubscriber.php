<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Funnel;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'referal',
        'is_customer',
        'token',
        'unsubscribed'
    ];

    protected $casts = [
        'is_customer'      => 'boolean',
        'unsubscribed'      => 'boolean',
    ];

    /**
     * The funnels this subscriber is subscribed to (pivot: funnel_subscriber).
     */
    public function funnels()
    {
        return $this->belongsToMany(Funnel::class, 'funnel_subscriber', 'subscriber_id', 'funnel_id')
                    ->withTimestamps();
    }
}
