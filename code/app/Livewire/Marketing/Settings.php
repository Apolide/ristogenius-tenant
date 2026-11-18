<?php

namespace App\Livewire\Marketing;

use Livewire\Component;

class Settings extends Component
{
    public array $settings = [];

    public function boot(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }

    public function mount(): void
    {
        $this->settings = app(\App\Services\Marketing\MarketingSettings::class)->get();
    }

    public function save(): void
    {
        app(\App\Services\Marketing\MarketingSettings::class)->save($this->settings);
        session()->flash('success', __('marketing.saved'));
    }

    public function render()
    {
        return view('livewire.marketing.settings', ['messages' => \App\Models\MessageOutbox::where('aggregate_type', 'marketing_confirmation')->latest('id')->limit(50)->get()])->title(__('marketing.settings'));
    }
}
