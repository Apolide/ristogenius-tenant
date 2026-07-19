<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\PublicFormSubmissionService;
use App\Services\TenantBrandingService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublicForm extends Component
{
    use WithFileUploads;

    public MarketingForm $form;

    public string $language;

    public array $answers = [];

    public bool $submitted = false;

    public function updatedAnswers(mixed $value, string $key): void
    {
        if (! in_array($this->form->type, ['booking', 'event'], true)) {
            return;
        }

        if ($key === 'date') {
            $this->answers['time'] = '';

            return;
        }

        if ($key === 'guests' && filled($this->answers['time'] ?? null)) {
            $availableSlots = $this->bookingSlots();
            if (! isset($availableSlots[$this->answers['time']])) {
                $this->answers['time'] = '';
            }
        }
    }

    public function mount(MarketingForm $form, string $language): void
    {
        abort_unless($form->is_active && in_array($language, $form->enabled_languages, true), 404);
        $this->form = $form;
        $this->language = $language;

        foreach ($form->fields()->where('visible', true)->where('type', 'checkbox')->get() as $field) {
            if (($field->options[$language] ?? []) !== []) {
                $this->answers[$field->key] = [];
            }
        }
    }

    public function submit(PublicFormSubmissionService $submissions): void
    {
        $bookingSlots = $this->bookingSlots();
        $rules = [];
        foreach ($this->form->fields()->where('visible', true)->get() as $f) {
            $r = $f->required ? ['required'] : ['nullable'];
            $localizedOptions = $f->options[$this->language] ?? [];
            if ($f->type === 'checkbox' && $localizedOptions !== []) {
                $r[] = 'array';
                if ($f->required) {
                    $r[] = 'min:1';
                }
                $rules['answers.'.$f->key] = $r;
                $rules['answers.'.$f->key.'.*'] = [Rule::in($localizedOptions)];

                continue;
            }
            $typeRules = match ($f->type) {
                'email' => ['email'],
                'number' => ['integer', 'min:1'],
                'date' => $f->key === 'date' ? ['date', 'after_or_equal:today'] : ['date'],
                'time' => in_array($this->form->type, ['booking', 'event'], true)
                    ? ['date_format:H:i', Rule::in(array_keys($bookingSlots))]
                    : ['date_format:H:i'],
                'checkbox' => $f->required ? ['accepted'] : ['boolean'],
                'select', 'radio' => [Rule::in($localizedOptions)],
                'file' => ['file', 'mimes:pdf,doc,docx', 'max:10240'],
                default => ['string', 'max:5000'],
            };
            $r = [...$r, ...$typeRules];
            $rules['answers.'.$f->key] = $r;
        }
        $payload = $this->validate($rules)['answers'];
        foreach ($this->form->fields()->where('visible', true)->where('type', 'file')->get() as $field) {
            if (isset($payload[$field->key])) {
                $payload[$field->key] = $payload[$field->key]->store("marketing-forms/{$this->form->id}", 'local');
            }
        }
        $submissions->submit($this->form, $payload, $this->language);
        $this->submitted = true;
        $this->answers = [];
    }

    public function render(TenantBrandingService $branding, CustomerLanguageService $languages)
    {
        app()->setLocale($this->language);

        return view('livewire.marketing.forms.public', [
            'fields' => $this->form->fields()->where('visible', true)->get(),
            'branding' => $branding->branding(),
            'languages' => collect($languages->enabled())->only($this->form->enabled_languages)->all(),
            'bookingSlots' => $this->bookingSlots(),
        ])->layout('layouts.customer-booking', ['title' => $this->form->translations[$this->language]['title']]);
    }

    private function bookingSlots(): array
    {
        if (! in_array($this->form->type, ['booking', 'event'], true)) {
            return [];
        }

        return app(BookingService::class)->availableSlots(
            (string) ($this->answers['date'] ?? ''),
            (int) ($this->answers['guests'] ?? 1),
        );
    }
}
