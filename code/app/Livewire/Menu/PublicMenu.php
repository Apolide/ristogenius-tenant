<?php

namespace App\Livewire\Menu;

use App\Models\DigitalMenu;
use App\Services\CustomerLanguageService;
use App\Services\TenantBrandingService;
use Livewire\Component;

class PublicMenu extends Component
{
    public DigitalMenu $menu;

    #[\Livewire\Attributes\Locked]
    public string $language;

    public function hydrate(): void
    {
        app()->setLocale($this->language);
    }

    public function mount(string $language, DigitalMenu $menu): void
    {
        abort_unless($menu->is_published && $menu->is_visible && in_array($language, $menu->enabled_languages, true)
            && isset(app(CustomerLanguageService::class)->enabled()[$language]), 404);
        $this->language = $language;
        app()->setLocale($language);
    }

    public function render(TenantBrandingService $branding, CustomerLanguageService $languageService)
    {
        app()->setLocale($this->language);
        $this->menu->load(['categories' => fn ($q) => $q->where('is_enabled', true)->with(['products' => fn ($q) => $q->wherePivot('is_visible', true)->where('is_active', true)->with(['recommendations' => fn ($q) => $q->where('is_active', true)->with('recommendedProduct')])])]);

        return view('livewire.menu.public-menu', [
            'coupons' => app(\App\Services\Marketing\CouponService::class)->visibleOnMenu($this->menu, $this->language),
            'branding' => $branding->branding(),
            'languages' => array_intersect_key($languageService->enabled(), array_flip($this->menu->enabled_languages)),
        ])->layout('layouts.digital-menu')->title($this->menu->translatedName($this->language));
    }
}
