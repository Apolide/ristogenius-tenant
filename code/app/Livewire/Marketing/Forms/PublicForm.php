<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\EventScheduleService;
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
                'number' => $this->numberRules($f->key),
                'date' => $this->dateRules($f->key),
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

        if ($this->form->type === 'event' && ($this->form->schedule['mode'] ?? null) !== 'standard') {
            return app(EventScheduleService::class)->availableSlots(
                $this->form,
                (string) ($this->answers['date'] ?? ''),
                (int) ($this->answers['guests'] ?? 1),
            );
        }

        return app(BookingService::class)->availableSlots(
            (string) ($this->answers['date'] ?? ''),
            (int) ($this->answers['guests'] ?? 1),
        );
    }

    private function dateRules(string $key): array
    {
        $rules = $key === 'date' ? ['date', 'after_or_equal:today'] : ['date'];
        if ($key === 'date' && $this->form->type === 'event' && ($this->form->schedule['mode'] ?? null) !== 'standard') {
            $rules[] = function (string $attribute, mixed $value, \Closure $fail): void {
                if (! app(EventScheduleService::class)->dateIsAvailable($this->form, (string) $value)) {
                    $fail('La data selezionata non è disponibile per questo evento.');
                }
            };
        }

        return $rules;
    }

    private function numberRules(string $key): array
    {
        if ($key !== 'guests' || $this->form->type !== 'event' || ! $this->eventUsesCustomSlots()) {
            return ['integer', 'min:1'];
        }

        $minimum = max(1, (int) ($this->form->schedule['min_guests'] ?? 1));
        $maximum = max($minimum, (int) ($this->form->schedule['max_guests'] ?? $minimum));

        return ['integer', "between:{$minimum},{$maximum}"];
    }

    private function eventUsesCustomSlots(): bool
    {
        $schedule = $this->form->schedule ?? [];

        return ($schedule['mode'] ?? null) !== 'standard'
            && ($schedule['slot_mode'] ?? (array_key_exists('slots', $schedule) ? 'custom' : 'standard')) === 'custom';
    }
}
