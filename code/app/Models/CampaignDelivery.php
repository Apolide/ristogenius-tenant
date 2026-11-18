<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignDelivery extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['queued_at' => 'datetime', 'started_at' => 'datetime', 'accepted_at' => 'datetime', 'clicked_at' => 'datetime'];

    public function run()
    {
        return $this->belongsTo(CampaignRun::class, 'campaign_run_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
