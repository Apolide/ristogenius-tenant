<?php

namespace App\Livewire\Marketing\Campaigns;

use App\Models\MarketingCampaign;
use App\Services\Marketing\MarketingAccess;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignIndex extends Component
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

    public function duplicate(string $id): void
    {
        $original = MarketingCampaign::findOrFail($id);
        $copy = $original->replicate();
        $copy->name .= ' · '.__('marketing.copy');
        $copy->save();
        $this->redirectRoute('campaigns.edit', ['campaign' => $copy]);
    }

    public function archive(string $id): void
    {
        $c = MarketingCampaign::findOrFail($id);
        $c->update(['archived' => ! $c->archived]);
    }

    public function render()
    {
        return view('livewire.marketing.campaigns.index', ['campaigns' => MarketingCampaign::withCount('recipients')->with(['runs' => fn ($q) => $q->latest()])->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))->latest()->paginate(15)])->title(__('marketing.campaigns'));
    }
}
