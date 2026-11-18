<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingPreference extends Model
{
    protected $guarded = [];

    protected $casts = ['access_token' => 'encrypted', 'unsubscribed_at' => 'datetime'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'customer_id';

    protected $hidden = ['token_hash', 'access_token'];
}
