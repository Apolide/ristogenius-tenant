<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingCampaign extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['translations' => 'array', 'enabled_languages' => 'array', 'filters' => 'array', 'archived' => 'boolean'];

    public function recipients()
    {
        return $this->belongsToMany(Customer::class, 'campaign_recipients');
    }

    public function runs()
    {
        return $this->hasMany(CampaignRun::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function form()
    {
        return $this->belongsTo(MarketingForm::class, 'marketing_form_id');
    }

    public function snapshot(): array
    {
        return $this->only(['name', 'type', 'translations', 'enabled_languages', 'image_path', 'target_url', 'coupon_id', 'marketing_form_id']);
    }
}
