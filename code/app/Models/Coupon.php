<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['translations' => 'array', 'enabled_languages' => 'array', 'rules' => 'array', 'benefit' => 'array', 'fields' => 'array', 'is_active' => 'boolean', 'show_in_menu' => 'boolean', 'claim_until' => 'datetime', 'valid_from' => 'date', 'valid_until' => 'date'];

    public function assignments()
    {
        return $this->hasMany(CouponAssignment::class);
    }

    public function form()
    {
        return $this->belongsTo(MarketingForm::class, 'marketing_form_id');
    }

    public function menus()
    {
        return $this->belongsToMany(DigitalMenu::class, 'coupon_menu');
    }

    public function title(?string $language = null): string
    {
        return $this->translations[$language ?? app()->getLocale()]['title'] ?? $this->name;
    }

    public function snapshot(): array
    {
        return array_replace($this->only(['name', 'slug', 'translations', 'enabled_languages', 'rules', 'benefit', 'marketing_form_id', 'image_path']), ['valid_from' => $this->valid_from?->toDateString(), 'valid_until' => $this->valid_until?->toDateString()]);
    }

    public function claimable(): bool
    {
        return $this->is_active && (! $this->claim_until || $this->claim_until->isFuture()) && (! $this->valid_until || $this->valid_until->copy()->endOfDay()->isFuture()) && ($this->max_assignments === null || $this->assigned_count < $this->max_assignments) && ($this->max_total_uses === null || $this->uses_count < $this->max_total_uses);
    }
}
