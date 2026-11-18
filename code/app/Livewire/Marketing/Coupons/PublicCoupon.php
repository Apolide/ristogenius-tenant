<?php

namespace App\Livewire\Marketing\Coupons;

use App\Models\Coupon;
use App\Services\Marketing\CouponService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class PublicCoupon extends Component
{
    #[Locked]
    public Coupon $coupon;

    #[Locked]
    public string $language;

    public array $answers = [];

    public bool $sent = false;

    public function mount(Coupon $coupon, string $language): void
    {
        $this->coupon = $coupon;
        $this->language = $language;
        $this->context();
    }

    public function hydrate(): void
    {
        $this->context();
    }

    private function context(): void
    {
        abort_unless(in_array($this->language, $this->coupon->enabled_languages, true) && array_key_exists($this->language, app(\App\Services\CustomerLanguageService::class)->enabled()), 404);
        app()->setLocale($this->language);
    }

    public function submit(): void
    {
        $this->context();
        $data = $this->validate([
            'answers.name' => 'required|string|max:255',
            'answers.email' => 'required|email|max:254',
            'answers.phone' => ['nullable', 'string', 'max:25', new \App\Rules\ValidPhoneNumber],
            'answers.privacy_consent' => 'accepted',
        ])['answers'];
        $data['_profile_required'] = true;
        app(CouponService::class)->request($this->coupon, $data, $this->language, session('marketing.delivery'));
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.marketing.coupons.public')->layout('layouts.customer-booking', ['title' => $this->coupon->title($this->language), 'fullWidth' => true]);
    }
}
