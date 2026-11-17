<?php

namespace App\Livewire\Marketing\Campaigns;

use App\Models\CampaignRun;
use App\Models\MarketingCampaign;
use App\Services\Marketing\CampaignService;
use App\Services\Marketing\MarketingAccess;
use App\Services\Marketing\MarketingMailer;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignSend extends Component
{
    use WithPagination;

    #[Locked]
    public string $campaignId;

    public string $language = 'it';

    public string $testEmail = '';

    public string $scheduledAt = '';

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function mount(MarketingCampaign $campaign): void
    {
        $this->campaignId = $campaign->id;
        $this->language = $campaign->enabled_languages[0];
        $this->scheduledAt = now(config('marketing.timezone'))->format('Y-m-d\TH:i');
    }

    public function testEmail(): void
    {
        $this->validate(['testEmail' => 'required|email', 'language' => 'in:it,en,de']);
        $c = MarketingCampaign::findOrFail($this->campaignId);
        app(CampaignService::class)->validateTarget($c->snapshot(), $this->language);
        $content = app(CampaignService::class)->content($c->snapshot(), $this->language, $this->testEmail);
        app(MarketingMailer::class)->enqueue('campaign-test:'.\Illuminate\Support\Str::uuid(), $this->testEmail, $this->language, $content['subject'], '', $content['url'] ?? '', $content);
        session()->flash('success', __('marketing.test_queued'));
    }

    public function schedule(bool $now = false): void
    {
        $this->validate(['scheduledAt' => 'required|date']);
        $when = $now ? now() : \Carbon\Carbon::parse($this->scheduledAt, config('marketing.timezone'));
        $run = app(CampaignService::class)->schedule(MarketingCampaign::findOrFail($this->campaignId), $when);
        session()->flash('success', __('marketing.scheduled'));
        $this->dispatch('$refresh');
    }

    public function control(string $id, string $action): void
    {
        $run = CampaignRun::where('marketing_campaign_id', $this->campaignId)->findOrFail($id);
        abort_unless(in_array($action, ['pause', 'resume', 'cancel', 'retry'], true), 422);
        if ($action === 'pause' && in_array($run->status, ['scheduled', 'running'])) {
            $run->update(['status' => 'paused']);
        }
        if ($action === 'resume' && $run->status === 'paused') {
            $run->deliveries()->where('status', 'pending')->update(['queued_at' => null]);
            $run->update(['status' => 'scheduled']);
        }
        if ($action === 'cancel') {
            $run->update(['status' => 'cancelled']);
            $run->deliveries()->where('status', 'pending')->update(['status' => 'cancelled']);
        }
        if ($action === 'retry' && $run->status !== 'cancelled') {
            $run->deliveries()->where('status', 'failed')->update(['status' => 'pending', 'queued_at' => null, 'error' => null]);
            $run->update(['status' => 'scheduled']);
        }
    }

    public function render()
    {
        $c = MarketingCampaign::findOrFail($this->campaignId);

        return view('livewire.marketing.campaigns.send', ['campaign' => $c, 'preview' => app(CampaignService::class)->content($c->snapshot(), $this->language, ''), 'runs' => $c->runs()->latest()->get(), 'recipients' => app(\App\Services\Marketing\CampaignAudience::class)->selected($c)->orderBy('customers.id')->paginate(15, ['customers.*'], 'recipientsPage')])->title(__('marketing.send'));
    }
}
