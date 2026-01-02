<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $fillable = ['region_id', 'name'];

    public $timestamps = false;

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function comunis()
    {
        return $this->hasMany(Comuni::class);
    }
    
}
