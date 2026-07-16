<?php

namespace App\Livewire\Settings;

use App\Services\Settings\TenantSettingMessageTemplatesService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MessageTemplatesIndex extends Component
{
    public string $search = '';

    public ?string $editingKey = null;

    public array $form = [];

    public function edit(string $key, TenantSettingMessageTemplatesService $messageTemplatesService): void
    {
        $template = $messageTemplatesService->messageTemplate($key);

        if (! $template) {
            return;
        }

        $this->editingKey = $key;
        $this->form = $template;
        $this->resetErrorBag();
    }

    public function abort(): void
    {
        $this->editingKey = null;
        $this->form = [];
        $this->resetErrorBag();
    }

    public function save(TenantSettingMessageTemplatesService $messageTemplatesService): void
    {
        if (! $this->editingKey) {
            return;
        }

        $languages = $messageTemplatesService->tenantLanguages();

        $rules = [
            'editingKey' => [Rule::in(array_keys(TenantSettingMessageTemplatesService::MESSAGE_TEMPLATE_DEFAULTS))],
            'form.translations' => ['required', 'array'],
        ];

        foreach ($languages as $language) {
            $rules["form.translations.{$language}.subject"] = ['required', 'string', 'max:255'];
            $rules["form.translations.{$language}.message"] = ['required', 'string'];
            $rules["form.translations.{$language}.additional_note"] = ['nullable', 'string'];
            $rules["form.translations.{$language}.sms"] = ['nullable', 'string', 'max:160'];
        }

        $this->validate($rules);

        $messageTemplatesService->updateMessageTemplate($this->editingKey, $this->form);
        $this->form = $messageTemplatesService->messageTemplate($this->editingKey) ?? [];

        session()->flash('success', 'Messaggio aggiornato correttamente.');
    }

    public function resetTemplate(TenantSettingMessageTemplatesService $messageTemplatesService): void
    {
        if (! $this->editingKey) {
            return;
        }

        $messageTemplatesService->resetMessageTemplate($this->editingKey);
        $this->form = $messageTemplatesService->messageTemplate($this->editingKey) ?? [];
        $this->resetErrorBag();

        session()->flash('success', 'Messaggio ripristinato correttamente.');
    }

    public function render(TenantSettingMessageTemplatesService $messageTemplatesService)
    {
        return view('livewire.settings.message-templates-index', [
            'languages' => $messageTemplatesService->languageMeta(),
            'templates' => $messageTemplatesService->messageTemplates($this->search),
        ])->title('Messaggi automatici');
    }
}
