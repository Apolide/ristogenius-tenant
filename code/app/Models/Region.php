<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{

    protected $fillable = ['name'];

    public $timestamps = false;

    public function province()
    {
        return $this->belongsTo(Province::class);
    }
}
