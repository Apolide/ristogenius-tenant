<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingFormField extends Model
{
    protected $fillable = ['key', 'type', 'label', 'options', 'required', 'visible', 'locked', 'position'];

    protected $casts = ['label' => 'array', 'options' => 'array', 'required' => 'boolean', 'visible' => 'boolean', 'locked' => 'boolean'];

    public function form()
    {
        return $this->belongsTo(MarketingForm::class, 'marketing_form_id');
    }
}
