<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponAssignment extends Model
{
    use \Illuminate\Database\Eloquent\Concerns\HasUuids;

    protected $guarded = [];

    protected $casts = ['snapshot' => 'array', 'profile' => 'encrypted:array', 'access_token' => 'encrypted', 'revoked_at' => 'datetime'];

    protected $hidden = ['access_token', 'token_hash', 'profile'];

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function url(?string $language = null): string
    {
        return route('coupons.card', ['language' => $language ?? $this->language, 'token' => $this->access_token]);
    }

    public function bookingUrl(?string $language = null): ?string
    {
        $form = MarketingForm::find($this->snapshot['marketing_form_id'] ?? null);
        $language ??= $this->language;

        return $form && $form->is_active && $form->accepts_coupons && in_array($language, $form->enabled_languages, true) ? route('marketing.forms.public', ['language' => $language, 'form' => $form->slug, 'coupon' => $this->access_token]) : null;
    }
}
