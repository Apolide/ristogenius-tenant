<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MarketingFormSubmission extends Model
{
    use HasUuids;

    protected $fillable = ['booking_id', 'language', 'field_snapshot', 'payload'];

    protected $casts = ['field_snapshot' => 'array', 'payload' => 'array'];

    public function form()
    {
        return $this->belongsTo(MarketingForm::class, 'marketing_form_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
