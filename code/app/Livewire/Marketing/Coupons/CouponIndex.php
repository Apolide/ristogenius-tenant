<?php

namespace App\Livewire\Marketing\Coupons;

use App\Models\Coupon;
use App\Services\Marketing\MarketingAccess;
use Livewire\Component;
use Livewire\WithPagination;

class CouponIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggle(string $id): void
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);
    }

    public function render()
    {
        return view('livewire.marketing.coupons.index', ['coupons' => Coupon::when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')->orWhere('translations', 'like', '%'.$this->search.'%')))->latest()->paginate(15)])->title(__('marketing.coupons'));
    }
}
