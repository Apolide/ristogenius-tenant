<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comuni extends Model
{
    protected $fillable = ['province_id', 'name'];

    public $timestamps = false;

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
    
}
