<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashBillPlan extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = ['pricing' => 'array', 'version' => 'integer'];

    public function shares()
    {
        return $this->hasMany(CashBillShare::class)->orderBy('position');
    }
}
