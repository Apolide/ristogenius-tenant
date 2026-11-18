<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Rules\ValidPhoneNumber;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\EventScheduleService;
use App\Services\MarketingForms\FormPublicAssetService;
use App\Services\MarketingForms\FormStyleService;
use App\Services\MarketingForms\PublicFormSubmissionService;
use App\Services\PhoneCountryService;
use App\Services\PhoneNumberService;
use App\Services\TenantBrandingService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublicForm extends Component
{
    use WithFileUploads;

    public MarketingForm $form;

    #[\Livewire\Attributes\Locked]
    public string $language;

    public array $answers = [];

    public bool $submitted = false;

    #[\Livewire\Attributes\Locked]
    public string $idempotencyKey;

    #[\Livewire\Attributes\Locked]
    public ?string $couponToken = null;

    public function couponAssignment(): ?\App\Models\CouponAssignment
    {
        return $this->couponToken ? app(\App\Services\Marketing\CouponService::class)->fromToken($this->couponToken) : null;
    }

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

    public function hydrate(): void
    {
        app()->setLocale($this->language);
    }

    public function mount(MarketingForm $form, string $language): void
    {
        abort_unless($form->is_active && in_array($language, $form->enabled_languages, true), 404);
        $this->form = $form;
        $this->idempotencyKey = (string) \Illuminate\Support\Str::uuid();
        $this->language = $language;
        $this->couponToken = request()->query('coupon');
        if ($assignment = $this->couponAssignment()) {
            abort_unless($form->accepts_coupons && ($assignment->snapshot['marketing_form_id'] ?? null) === $form->id, 404);
            $this->answers = ['name' => $assignment->customer->display_name, 'email' => $assignment->customer->email, 'phone' => $assignment->customer->phone];
        }

        if ($form->fields()->where('visible', true)->where('key', 'phone')->exists()) {
            $this->answers['phone_region'] = strtoupper((string) config('app.default_phone_region', 'IT'));
        }

        foreach ($form->fields()->where('visible', true)->where('type', 'checkbox')->get() as $field) {
            if (($field->options[$language] ?? []) !== []) {
                $this->answers[$field->key] = [];
            }
        }
    }

    public function submit(PublicFormSubmissionService $submissions): void
    {
        app()->setLocale($this->language);
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
                'tel' => $f->key === 'phone'
                    ? ['string', 'max:25', new ValidPhoneNumber((string) ($this->answers['phone_region'] ?? 'IT'))]
                    : ['string', 'max:25'],
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
        if ($this->form->fields()->where('visible', true)->where('key', 'phone')->exists()) {
            $rules['answers.phone_region'] = ['required_with:answers.phone', Rule::in(array_column(app(PhoneCountryService::class)->countries(), 'region'))];
        }
        if ($assignment = $this->couponAssignment()) {
            if (($assignment->snapshot['benefit']['quantity_basis'] ?? 'fixed') === 'children') {
                $rules['answers.children'] = 'required|integer|min:0|lte:answers.guests';
            }
        }
        $payload = $this->validate($rules)['answers'];
        if (array_key_exists('phone', $payload)) {
            $payload['phone'] = app(PhoneNumberService::class)->normalize($payload['phone'], $payload['phone_region'] ?? 'IT');
            unset($payload['phone_region']);
        }
        foreach ($this->form->fields()->where('visible', true)->where('type', 'file')->get() as $field) {
            if (isset($payload[$field->key])) {
                $payload[$field->key] = $payload[$field->key]->store("marketing-forms/{$this->form->id}", 'local');
            }
        }
        $submissions->submit($this->form, $payload, $this->language, $this->couponAssignment(), $this->idempotencyKey);
        $this->submitted = true;
        $this->answers = [];
    }

    public function render(TenantBrandingService $branding, CustomerLanguageService $languages, FormStyleService $styles, FormPublicAssetService $assets)
    {
        app()->setLocale($this->language);

        return view('livewire.marketing.forms.public', [
            'fields' => $this->form->fields()->where('visible', true)->get(),
            'branding' => $branding->branding(),
            'languages' => collect($languages->enabled())->only($this->form->enabled_languages)->all(),
            'bookingSlots' => $this->bookingSlots(),
            'countries' => app(PhoneCountryService::class)->countries(),
            'formStyle' => $styles->for($this->form),
            'formStyleVariables' => $styles->cssVariables($styles->for($this->form)),
            'backgroundUrl' => $assets->backgroundUrl($this->form),
        ])->layout('layouts.customer-booking', ['title' => $this->form->translations[$this->language]['title'], 'fullWidth' => true]);
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
                    $fail(__('marketing_forms.event_date_unavailable'));
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
