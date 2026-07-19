<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MarketingFormSubmission extends Model
{
    use HasUuids;

    protected $fillable = ['language', 'payload'];

    protected $casts = ['payload' => 'array'];

    public function form()
    {
        return $this->belongsTo(MarketingForm::class, 'marketing_form_id');
    }
}
