<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignRun extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'scheduled_at' => 'datetime'];

    public function campaign()
    {
        return $this->belongsTo(MarketingCampaign::class, 'marketing_campaign_id');
    }

    public function deliveries()
    {
        return $this->hasMany(CampaignDelivery::class);
    }
}
