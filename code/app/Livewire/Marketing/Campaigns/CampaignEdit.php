<?php

namespace App\Livewire\Marketing\Campaigns;

use App\Models\Comuni;
use App\Models\Coupon;
use App\Models\MarketingCampaign;
use App\Models\MarketingForm;
use App\Models\Province;
use App\Services\Marketing\CampaignAudience;
use App\Services\Marketing\CampaignService;
use App\Services\Marketing\MarketingAccess;
use App\Services\Marketing\MarketingContent;
use App\Services\Marketing\MarketingSettings;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CampaignEdit extends Component
{
    use WithFileUploads,WithPagination;

    #[Locked]
    public ?string $campaignId = null;

    public array $data = [];

    public array $filters = [];

    public $image;

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function mount($campaign = null): void
    {
        if (is_string($campaign)) {
            $campaign = MarketingCampaign::findOrFail($campaign);
        }
        $this->data = ['name' => '', 'type' => 'information', 'translations' => array_fill_keys(['it', 'en', 'de'], ['subject' => '', 'preheader' => '', 'html' => '', 'whatsapp' => '', 'telegram' => '', 'sms' => '', 'button' => '']), 'enabled_languages' => ['it', 'en', 'de'], 'target_url' => null, 'coupon_id' => null, 'marketing_form_id' => null];
        if ($campaign?->exists) {
            $this->campaignId = $campaign->id;
            $this->data = array_replace($this->data, $campaign->only(array_keys($this->data)));
            $this->filters = $campaign->filters ?? [];
        }
    }

    public function updatedFilters(): void
    {
        $this->resetPage();
    }

    public function attach(): void
    {
        app(CampaignService::class)->attach(MarketingCampaign::findOrFail($this->campaignId), $this->filters);
        session()->flash('success', __('marketing.recipients_updated'));
    }

    public function toggleRecipient(string $id): void
    {
        $campaign = MarketingCampaign::findOrFail($this->campaignId);
        if ($campaign->recipients()->whereKey($id)->exists()) {
            $campaign->recipients()->detach($id);

            return;
        }
        $customer = \App\Models\Customer::findOrFail($id);
        app(\App\Services\Marketing\CouponService::class)->check(app(CampaignAudience::class)->allowed($customer), 'ineligible');
        $campaign->recipients()->syncWithoutDetaching([$id]);
    }

    public function remove(string $id): void
    {
        MarketingCampaign::findOrFail($this->campaignId)->recipients()->detach($id);
    }

    public function clearRecipients(): void
    {
        MarketingCampaign::findOrFail($this->campaignId)->recipients()->detach();
    }

    public function save(): void
    {
        $max = app(MarketingSettings::class)->get();
        $rules = ['data.name' => 'required|string|max:255', 'data.type' => 'in:information,coupon,booking,coupon_booking,link', 'data.enabled_languages' => 'required|array|min:1', 'data.enabled_languages.*' => 'in:it,en,de',
            'data.translations' => 'required|array', 'data.translations.*.subject' => 'nullable|string|max:255', 'data.translations.*.preheader' => 'nullable|string|max:255', 'data.translations.*.html' => 'nullable|string|max:100000', 'data.translations.*.button' => 'nullable|string|max:100',
            'data.translations.*.whatsapp' => ['nullable', 'string', app(MarketingContent::class)->textLength($max['whatsapp_max'])], 'data.translations.*.telegram' => ['nullable', 'string', app(MarketingContent::class)->textLength($max['telegram_max'])], 'data.translations.*.sms' => ['nullable', 'string', app(MarketingContent::class)->textLength($max['sms_max'])],
            'data.target_url' => 'nullable|url:http,https|max:2000', 'data.coupon_id' => 'nullable|uuid|exists:coupons,id', 'data.marketing_form_id' => 'nullable|uuid|exists:marketing_forms,id', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', 'filters' => 'array'];
        foreach (array_intersect((array) ($this->data['enabled_languages'] ?? []), ['it', 'en', 'de']) as $lang) {
            $rules["data.translations.$lang.subject"] = 'required|string|max:255';
            $rules["data.translations.$lang.html"] = ['required', 'string', 'max:100000', function ($attribute, $value, $fail) {
                $text = html_entity_decode(strip_tags(app(MarketingContent::class)->clean($value)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if (preg_replace('/[\s\x{00a0}\x{200b}]+/u', '', $text) === '') {
                    $fail(__('marketing.content_required'));
                }
            }];
        }
        $v = $this->validate($rules, [], [
            'data.translations.*.subject' => __('marketing.subject'),
            'data.translations.*.html' => __('marketing.email_body'),
        ])['data'];
        $v['translations'] = app(MarketingContent::class)->translations($v['translations']);
        foreach (['target_url', 'coupon_id', 'marketing_form_id'] as $field) {
            if (empty($v[$field])) {
                $v[$field] = null;
            }
        }
        if (in_array($v['type'], ['coupon', 'coupon_booking'], true)) {
            app(\App\Services\Marketing\CouponService::class)->check((bool) $v['coupon_id'], 'unavailable');
        }
        if ($v['type'] === 'booking') {
            app(\App\Services\Marketing\CouponService::class)->check((bool) $v['marketing_form_id'], 'form_incompatible');
        }
        if ($v['type'] === 'link') {
            app(\App\Services\Marketing\CouponService::class)->check((bool) $v['target_url'], 'invalid_link');
        }
        if ($this->image) {
            $v['image_path'] = app(MarketingContent::class)->image($this->image);
        }
        $c = $this->campaignId ? MarketingCampaign::findOrFail($this->campaignId) : new MarketingCampaign;
        $c->fill($v + ['filters' => $this->filters])->save();
        session()->flash('success', __('marketing.saved'));
        $this->redirectRoute('campaigns.edit', ['campaign' => $c]);
    }

    public function render()
    {
        $campaign = $this->campaignId ? MarketingCampaign::find($this->campaignId) : null;
        $audience = app(CampaignAudience::class);

        return view('livewire.marketing.campaigns.edit', ['campaign' => $campaign, 'selectedRecipients' => $campaign ? $audience->selected($campaign)->orderBy('customers.id')->paginate(15, ['customers.*'], 'selectedPage') : null, 'coupons' => Coupon::all(), 'forms' => MarketingForm::whereIn('type', ['booking', 'event'])->get(), 'limits' => app(MarketingSettings::class)->get(), 'customers' => app(CampaignAudience::class)->query($this->filters)->orderBy('customers.id')->paginate(15), 'eligibleCount' => app(CampaignAudience::class)->query($this->filters)->count(), 'provinces' => Province::orderBy('name')->get(), 'cities' => ! empty($this->filters['provinceSelected']) ? Comuni::where('province_id', $this->filters['provinceSelected'])->orderBy('name')->get() : collect()])->title(__('marketing.campaign_editor'));
    }
}
