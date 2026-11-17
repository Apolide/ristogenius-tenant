<?php

namespace App\Livewire\Marketing\Coupons;

use App\Models\Comuni;
use App\Models\Province;
use App\Services\Marketing\CouponClaimFlow;
use App\Services\Marketing\CouponService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CompleteCoupon extends Component
{
    #[Locked]
    public string $token;

    #[Locked]
    public string $language;

    public array $answers = [];

    public function mount(string $token, string $language): void
    {
        $this->token = $token;
        $this->language = $language;
        $claim = app(CouponClaimFlow::class)->claim($token, $language);
        app()->setLocale($language);
        $this->answers = app(CouponClaimFlow::class)->prefill($claim);
    }

    public function updatedAnswers($value, $key): void
    {
        if ($key === 'province_id') {
            $this->answers['comuni_id'] = '';
        }
    }

    public function complete(): void
    {
        // Re-read the claim on every action: no client-side identity is authoritative.
        app(CouponClaimFlow::class)->claim($this->token, $this->language);
        $this->resetValidation();
        $assignment = app(CouponService::class)->confirm($this->token, $this->answers, $this->language);
        $this->redirect($assignment->url($this->language));
    }

    public function render()
    {
        $claim = app(CouponClaimFlow::class)->claim($this->token, $this->language);
        app()->setLocale($this->language);

        return view('livewire.marketing.coupons.complete', [
            'coupon' => $claim->coupon,
            'verifiedEmail' => $claim->identity['email'],
            'alreadyAssigned' => $claim->coupon_assignment_id ? \App\Models\CouponAssignment::find($claim->coupon_assignment_id) : null,
            'provinces' => Province::orderBy('name')->get(),
            'cities' => ! empty($this->answers['province_id']) ? Comuni::where('province_id', $this->answers['province_id'])->orderBy('name')->get() : collect(),
        ]);
    }
}
