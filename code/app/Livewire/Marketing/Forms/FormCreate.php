<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\User;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\FormBlueprintService;
use Illuminate\Support\Str;
use Livewire\Component;

class FormCreate extends Component
{
    public string $type = 'booking';

    public bool $multilingual = false;

    public bool $is_active = true;

    public bool $accepts_coupons = true;

    public array $translations = [];

    public array $notify_user_ids = [];

    public string $event_mode = 'single';

    public array $schedule = [];

    public function mount(CustomerLanguageService $languages): void
    {
        $enabledLanguages = $languages->enabled();
        $this->multilingual = count($enabledLanguages) > 1;

        foreach ($enabledLanguages as $code => $meta) {
            $this->translations[$code] = ['title' => '', 'description' => ''];
        }
    }

    public function save(FormBlueprintService $blueprints, CustomerLanguageService $languageService)
    {
        $available = array_keys($languageService->enabled());
        $enabled = $this->multilingual ? $available : [($available[0] ?? 'it')];
        $rules = ['type' => ['required', 'in:booking,event,generic'], 'translations.'.$enabled[0].'.title' => ['required', 'string', 'max:255'], 'translations.'.$enabled[0].'.description' => ['nullable', 'string'], 'notify_user_ids' => ['array'], 'notify_user_ids.*' => ['integer', 'exists:users,id']];
        foreach (array_slice($enabled, 1) as $code) {
            $rules['translations.'.$code.'.title'] = ['required', 'string', 'max:255'];
            $rules['translations.'.$code.'.description'] = ['nullable', 'string'];
        }
        $this->validate($rules);
        $title = $this->translations[$enabled[0]]['title'];
        $form = $blueprints->create(['type' => $this->type, 'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)), 'translations' => array_intersect_key($this->translations, array_flip($enabled)), 'enabled_languages' => $enabled, 'schedule' => $this->type === 'event' ? ($this->schedule + ['mode' => $this->event_mode]) : null, 'is_active' => $this->is_active, 'accepts_coupons' => $this->accepts_coupons, 'notify_user_ids' => $this->notify_user_ids]);

        return $this->redirectRoute('marketing.forms.edit', $form);
    }

    public function render(FormBlueprintService $blueprints, CustomerLanguageService $languages)
    {
        return view('livewire.marketing.forms.create', ['types' => $blueprints->types(), 'languages' => $languages->enabled(), 'users' => User::where('enabled', true)->orderBy('name')->get()])->title(__('marketing_forms.create'));
    }
}
