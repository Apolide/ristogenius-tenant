<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MarketingForm extends Model
{
    use HasUuids;

    protected $fillable = ['type', 'slug', 'translations', 'enabled_languages', 'schedule', 'image_path', 'is_active', 'accepts_coupons'];

    protected $casts = ['translations' => 'array', 'enabled_languages' => 'array', 'schedule' => 'array', 'is_active' => 'boolean', 'accepts_coupons' => 'boolean'];

    public function fields()
    {
        return $this->hasMany(MarketingFormField::class)->orderBy('position');
    }

    public function notificationUsers()
    {
        return $this->belongsToMany(User::class, 'marketing_form_notification_user');
    }

    public function submissions()
    {
        return $this->hasMany(MarketingFormSubmission::class);
    }

    public function title(?string $language = null): string
    {
        $language ??= $this->enabled_languages[0] ?? 'it';

        return $this->translations[$language]['title'] ?? '';
    }
}
