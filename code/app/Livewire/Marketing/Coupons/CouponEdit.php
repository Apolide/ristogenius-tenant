<?php

namespace App\Livewire\Marketing\Coupons;

use App\Models\Coupon;
use App\Models\CouponAssignment;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuProduct;
use App\Models\MarketingForm;
use App\Services\Marketing\CouponService;
use App\Services\Marketing\MarketingAccess;
use App\Services\Marketing\MarketingContent;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CouponEdit extends Component
{
    use \Livewire\WithPagination;
    use WithFileUploads;

    public string $assignmentSearch = '';

    public function updatedAssignmentSearch(): void
    {
        $this->resetPage('assignmentsPage');
    }

    #[Locked]
    public ?string $couponId = null;

    public array $data = [];

    public array $menuIds = [];

    public $image;

    public string $usageMode = 'single';

    public string $benefitValue = '10.00';

    public string $maxDiscount = '';

    public array $newField = ['key' => '', 'type' => 'text', 'required' => false, 'visible' => true, 'label' => [], 'options' => []];

    public array $editingField = [];

    #[Locked]
    public ?int $editingFieldIndex = null;

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function mount($coupon = null): void
    {
        if (is_string($coupon)) {
            $coupon = Coupon::findOrFail($coupon);
        }
        $this->data = ['name' => '', 'slug' => '', 'translations' => array_fill_keys(['it', 'en', 'de'], ['title' => '', 'description' => '', 'terms' => '']), 'enabled_languages' => ['it', 'en', 'de'], 'rules' => ['weekdays' => [], 'min_pax' => 1, 'max_pax' => null, 'max_uses' => 1],
            'benefit' => ['type' => 'percent', 'value' => 1000, 'product_ids' => [], 'quantity' => 1, 'quantity_basis' => 'fixed', 'basis' => 'package', 'max_discount' => null], 'fields' => CouponService::fields(), 'is_active' => false, 'show_in_menu' => false, 'marketing_form_id' => null, 'claim_until' => null, 'valid_from' => null, 'valid_until' => null, 'max_assignments' => null, 'max_total_uses' => null];
        if ($coupon?->exists) {
            $this->couponId = $coupon->id;
            $this->data = array_replace($this->data, $coupon->only(array_keys($this->data)));
            $this->data['claim_until'] = $coupon->claim_until?->format('Y-m-d\TH:i');
            $this->data['valid_from'] = $coupon->valid_from?->toDateString();
            $this->data['valid_until'] = $coupon->valid_until?->toDateString();
            $this->menuIds = $coupon->menus()->pluck('digital_menu_menus.id')->all();
            $this->benefitValue = number_format(($this->data['benefit']['value'] ?? 0) / 100, 2, '.', '');
            $this->maxDiscount = empty($this->data['benefit']['max_discount']) ? '' : number_format($this->data['benefit']['max_discount'] / 100, 2, '.', '');
            $max = $this->data['rules']['max_uses'] ?? null;
            $this->usageMode = $max === null ? 'unlimited' : ($max === 1 ? 'single' : 'limited');
        }
    }

    public function addOption(int $index): void
    {
        foreach (['it', 'en', 'de'] as $l) {
            $this->data['fields'][$index]['options'][$l][] = '';
        }
    }

    public function removeOption(int $index, int $option): void
    {
        foreach (['it', 'en', 'de'] as $l) {
            unset($this->data['fields'][$index]['options'][$l][$option]);
            $this->data['fields'][$index]['options'][$l] = array_values($this->data['fields'][$index]['options'][$l]);
        }
    }

    public function addField(): void
    {
        $this->data['fields'][] = $this->fieldDraft('newField');
        $this->newField = ['key' => '', 'type' => 'text', 'required' => false, 'visible' => true, 'label' => [], 'options' => []];
    }

    public function startEditingField(int $index): void
    {
        abort_unless(isset($this->data['fields'][$index]), 404);
        $this->editingFieldIndex = $index;
        $this->editingField = $this->data['fields'][$index];
    }

    public function cancelEditingField(): void
    {
        $this->editingFieldIndex = null;
        $this->editingField = [];
    }

    public function saveEditingField(): void
    {
        if ($this->editingFieldIndex === null) {
            return;
        }
        $original = $this->data['fields'][$this->editingFieldIndex];
        $field = $this->fieldDraft('editingField', $this->editingFieldIndex);
        if (in_array($original['key'], array_column(CouponService::fields(), 'key'), true)) {
            $field['key'] = $original['key'];
            $field['type'] = $original['type'];
        }
        if (in_array($field['key'], ['name', 'email', 'privacy_consent'], true)) {
            $field['required'] = $field['visible'] = true;
        }
        if ($field['key'] === 'marketing_consent') {
            $field['required'] = false;
        }
        $this->data['fields'][$this->editingFieldIndex] = $field;
        $this->cancelEditingField();
    }

    private function fieldDraft(string $prefix, ?int $index = null): array
    {
        $this->validate([
            "$prefix.key" => ['required', 'alpha_dash', 'max:60', 'not_regex:/^_/', Rule::notIn(collect($this->data['fields'])->except($index === null ? [] : [$index])->pluck('key')->all())],
            "$prefix.type" => 'required|in:text,textarea,email,tel,date,time,province,city,checkbox,number,select,radio',
            "$prefix.required" => 'boolean', "$prefix.visible" => 'boolean',
            "$prefix.label" => 'array', "$prefix.label.*" => 'nullable|string|max:255',
            "$prefix.options" => 'array', "$prefix.options.*" => 'array', "$prefix.options.*.*" => 'string|max:255',
        ]);
        $field = $this->{$prefix};

        return array_intersect_key($field, array_flip(['key', 'type', 'required', 'visible', 'label', 'options']));
    }

    public function addDraftOption(string $prefix): void
    {
        abort_unless(in_array($prefix, ['newField', 'editingField'], true), 422);
        foreach (['it', 'en', 'de'] as $language) {
            $this->{$prefix}['options'][$language][] = '';
        }
    }

    public function removeDraftOption(string $prefix, int $index): void
    {
        abort_unless(in_array($prefix, ['newField', 'editingField'], true), 422);
        foreach (['it', 'en', 'de'] as $language) {
            unset($this->{$prefix}['options'][$language][$index]);
            $this->{$prefix}['options'][$language] = array_values($this->{$prefix}['options'][$language]);
        }
    }

    public function removeField(int $index): void
    {
        if (isset($this->data['fields'][$index]) && ! in_array($this->data['fields'][$index]['key'], array_column(CouponService::fields(), 'key'), true)) {
            unset($this->data['fields'][$index]);
            $this->data['fields'] = array_values($this->data['fields']);
            $this->cancelEditingField();
        }
    }

    public function revoke(string $id): void
    {
        CouponAssignment::where('coupon_id', $this->couponId)->findOrFail($id)->update(['revoked_at' => now()]);
    }

    public function save(): void
    {
        $this->validate(['benefitValue' => 'required|numeric|min:0|max:1000000', 'maxDiscount' => 'nullable|numeric|min:0|max:1000000', 'usageMode' => 'in:single,limited,unlimited']);
        $this->data['benefit']['value'] = \App\Services\Orders\OrderService::cents($this->benefitValue);
        $this->data['benefit']['max_discount'] = $this->maxDiscount === '' ? null : \App\Services\Orders\OrderService::cents($this->maxDiscount);
        if ($this->usageMode !== 'limited') {
            $this->data['rules']['max_uses'] = $this->usageMode === 'single' ? 1 : null;
        } else {
            $this->validate(['data.rules.max_uses' => 'required|integer|min:1|max:100000']);
        }
        $rules = [
            'data.name' => 'required|string|max:255', 'data.slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('coupons', 'slug')->ignore($this->couponId)],
            'data.enabled_languages' => 'required|array|min:1', 'data.enabled_languages.*' => 'in:it,en,de',
            'data.translations' => 'required|array', 'data.translations.*.title' => 'nullable|string|max:255', 'data.translations.*.description' => 'nullable|string|max:50000', 'data.translations.*.terms' => 'nullable|string|max:20000',
            'data.rules' => 'required|array', 'data.rules.weekdays' => 'array', 'data.rules.weekdays.*' => 'integer|between:1,7', 'data.rules.min_pax' => 'required|integer|min:1|max:1000', 'data.rules.max_pax' => 'nullable|integer|gte:data.rules.min_pax|max:1000', 'data.rules.max_uses' => 'nullable|integer|min:1|max:100000',
            'data.benefit.type' => 'required|in:gift,percent,amount,fixed_price', 'data.benefit.value' => 'required|integer|min:0|max:100000000', 'data.benefit.product_ids' => 'array', 'data.benefit.product_ids.*' => 'uuid|exists:digital_menu_products,id',
            'data.benefit.quantity' => 'required|integer|min:1|max:1000', 'data.benefit.quantity_basis' => 'in:fixed,children', 'data.benefit.basis' => 'in:package,person,item', 'data.benefit.max_discount' => 'nullable|integer|min:0|max:100000000',
            'data.fields' => 'required|array|max:40', 'data.fields.*.key' => ['required', 'alpha_dash', 'max:60', 'distinct', 'not_regex:/^_/'], 'data.fields.*.type' => 'required|in:text,textarea,email,tel,date,time,province,city,checkbox,number,select,radio',
            'data.fields.*.required' => 'boolean', 'data.fields.*.visible' => 'boolean', 'data.fields.*.label' => 'array', 'data.fields.*.label.*' => 'nullable|string|max:255', 'data.fields.*.options' => 'array', 'data.fields.*.options.*' => 'array', 'data.fields.*.options.*.*' => 'string|max:255',
            'data.is_active' => 'boolean', 'data.show_in_menu' => 'boolean', 'data.marketing_form_id' => 'nullable|uuid|exists:marketing_forms,id', 'data.claim_until' => 'nullable|date', 'data.valid_from' => 'nullable|date', 'data.valid_until' => 'nullable|date|after_or_equal:data.valid_from',
            'data.max_assignments' => 'nullable|integer|min:1', 'data.max_total_uses' => 'nullable|integer|min:1', 'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', 'menuIds' => 'array', 'menuIds.*' => 'uuid|exists:digital_menu_menus,id',
        ];
        $values = $this->validate($rules)['data'];
        $check = app(CouponService::class);
        foreach ($values['enabled_languages'] as $language) {
            $check->check(filled($values['translations'][$language]['title'] ?? '') && filled(strip_tags($values['translations'][$language]['description'] ?? '')), 'language_missing');
        }
        $check->check($values['benefit']['type'] !== 'percent' || $values['benefit']['value'] <= 10000, 'invalid_benefit');
        $check->check($values['benefit']['type'] !== 'gift' || count($values['benefit']['product_ids']) > 0, 'gift_products');
        foreach (CouponService::fields() as $base) {
            $field = collect($values['fields'])->firstWhere('key', $base['key']);
            $check->check($field && $field['type'] === $base['type'], 'invalid_fields');
        }
        foreach (['name', 'email', 'privacy_consent'] as $key) {
            $i = collect($values['fields'])->search(fn ($f) => $f['key'] === $key);
            $values['fields'][$i]['required'] = true;
            $values['fields'][$i]['visible'] = true;
        }
        $i = collect($values['fields'])->search(fn ($f) => $f['key'] === 'marketing_consent');
        $values['fields'][$i]['required'] = false;
        if ($values['marketing_form_id']) {
            $form = MarketingForm::findOrFail($values['marketing_form_id']);
            $check->check($form->accepts_coupons && in_array($form->type, ['booking', 'event'], true) && ! array_diff($values['enabled_languages'], $form->enabled_languages), 'form_incompatible');
        }
        $values['translations'] = app(MarketingContent::class)->translations($values['translations']);
        foreach (['marketing_form_id', 'claim_until', 'valid_from', 'valid_until', 'max_assignments', 'max_total_uses'] as $key) {
            if (empty($values[$key])) {
                $values[$key] = null;
            }
        }
        if ($this->image) {
            $values['image_path'] = app(MarketingContent::class)->image($this->image);
        }
        $coupon = $this->couponId ? Coupon::findOrFail($this->couponId) : new Coupon;
        $check->check($values['max_assignments'] === null || $values['max_assignments'] >= $coupon->assigned_count, 'quota_too_small');
        $check->check($values['max_total_uses'] === null || $values['max_total_uses'] >= $coupon->uses_count, 'quota_too_small');
        \Illuminate\Support\Facades\DB::transaction(function () use ($coupon, $values, $check) {
            if ($coupon->exists) {
                $coupon = Coupon::lockForUpdate()->findOrFail($coupon->id);
            }
            $check->check($values['max_assignments'] === null || $values['max_assignments'] >= $coupon->assigned_count, 'quota_too_small');
            $check->check($values['max_total_uses'] === null || $values['max_total_uses'] >= $coupon->uses_count, 'quota_too_small');
            $coupon->fill($values)->save();
            $coupon->menus()->sync($this->menuIds);
        });
        session()->flash('success', __('marketing.saved'));
        $this->redirectRoute('coupons.edit', ['coupon' => $coupon]);
    }

    public function render()
    {
        return view('livewire.marketing.coupons.edit', ['forms' => MarketingForm::whereIn('type', ['booking', 'event'])->where('accepts_coupons', true)->get(), 'menus' => DigitalMenu::all(), 'products' => DigitalMenuProduct::orderBy('id')->get(), 'coupon' => $this->couponId ? Coupon::find($this->couponId) : null, 'assignments' => $this->couponId ? CouponAssignment::with('customer')->where('coupon_id', $this->couponId)->whereHas('customer', fn ($q) => $q->where(fn ($q) => $q->where('display_name', 'like', '%'.$this->assignmentSearch.'%')->orWhere('email', 'like', '%'.$this->assignmentSearch.'%')))->latest()->paginate(15, ['*'], 'assignmentsPage') : collect()])->title(__('marketing.coupon_editor'));
    }
}
