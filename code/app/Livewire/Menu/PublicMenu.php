<?php

namespace App\Livewire\Menu;

use App\Models\DigitalMenu;
use Livewire\Component;

class PublicMenu extends Component
{
    public DigitalMenu $menu;

    public string $language;

    public function mount(string $language, DigitalMenu $menu): void
    {
        abort_unless($menu->is_published && $menu->is_visible && in_array($language, $menu->enabled_languages, true), 404);
        $this->language = $language;
        app()->setLocale($language);
    }

    public function render()
    {
        $this->menu->load(['categories' => fn ($q) => $q->where('is_enabled', true)->with(['products' => fn ($q) => $q->wherePivot('is_visible', true)->where('is_active', true)->with(['recommendations' => fn ($q) => $q->where('is_active', true)->with('recommendedProduct')])])]);

        return view('livewire.menu.public-menu')->layout('layouts.digital-menu')->title($this->menu->translatedName($this->language));
    }
}
