<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasUuids;

    public const TYPES = ['rating', 'text', 'number', 'yes_no', 'choice', 'checkbox'];

    protected $fillable = ['label', 'reward_coupon_id', 'enabled_at', 'send_after_hours', 'review_enabled', 'review_min_minutes', 'review_max_minutes', 'default_language', 'translations', 'questions'];

    protected $casts = ['enabled_at' => 'datetime', 'review_enabled' => 'boolean', 'translations' => 'array', 'questions' => 'array'];

    public function invitations(): HasMany
    {
        return $this->hasMany(PollInvitation::class);
    }
}
