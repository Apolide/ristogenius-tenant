<div class="content"><div class="main-content" x-data="{ language: 'it' }">
<div class="flex flex-wrap items-center justify-between gap-3 mb-6"><a class="ti-btn ti-btn-light" href="{{ route('coupons.index') }}">← {{ __('marketing.coupons') }}</a><h5 class="page-title">{{ __('marketing.coupon_editor') }}</h5></div>
@include('marketing.errors')
@if($coupon && $coupon->assigned_count)<p class="alert alert-info">{{ __('marketing.version_notice') }}</p>@endif
<form wire:submit="save" class="space-y-5">
<div class="box"><div class="box-header"><h2 class="box-title">{{ __('marketing.internal_name') }} / {{ __('marketing.type') }}</h2></div><div class="box-body grid grid-cols-1 md:grid-cols-2 gap-6">
<label class="block mb-2 font-medium">{{ __('marketing.internal_name') }}<input class="form-control mt-2" wire:model="data.name" required maxlength="255"></label>
<label class="block mb-2 font-medium">{{ __('marketing.slug') }}<input class="form-control mt-2" wire:model="data.slug" required maxlength="120"></label>
<label class="block mb-2 font-medium"><input type="checkbox" class="form-check-input" wire:model="data.is_active"> {{ __('marketing.active') }}</label><label class="block mb-2 font-medium"><input type="checkbox" class="form-check-input" wire:model="data.show_in_menu"> {{ __('marketing.show_menu') }}</label>
<label class="block mb-2 font-medium">{{ __('marketing.image') }}<input type="file" class="form-control mt-2" wire:model="image" accept="image/jpeg,image/png,image/webp"></label>
<label class="block mb-2 font-medium">{{ __('marketing.booking_form') }}<select class="form-control mt-2" wire:model="data.marketing_form_id"><option value="">—</option>@foreach($forms as $form)<option value="{{ $form->id }}">{{ $form->title(app()->getLocale()) ?: $form->title() }}</option>@endforeach</select></label>
<label class="block mb-2 font-medium">{{ __('marketing.menu_scope') }}<select class="form-control mt-2" wire:model="menuIds" multiple>@foreach($menus as $menu)<option value="{{ $menu->id }}">{{ $menu->translatedName() }}</option>@endforeach</select><small>{{ __('marketing.all_menus') }}</small></label>
@if($coupon?->image_path)<img src="{{ asset($coupon->image_path) }}" alt="{{ __('marketing.image') }}" class="max-h-40">@endif
</div></div>
<div class="box"><div class="box-header"><h2 class="box-title">{{ __('marketing.language') }}</h2></div><div class="box-body space-y-6">
@include('livewire.marketing.partials.language-tabs')
@foreach(['it','en','de'] as $l)<section x-show="language==='{{ $l }}'" @if($l!=='it') x-cloak @endif role="tabpanel" id="marketing-panel-{{ $l }}" aria-labelledby="marketing-tab-{{ $l }}" class="space-y-4 border border-defaultborder rounded p-4">
<label class="block mb-2 font-medium">{{ __('marketing.title') }}<input class="form-control mt-2" wire:model="data.translations.{{ $l }}.title" maxlength="255"></label>
<label class="block mb-2 font-medium">{{ __('marketing.description') }}</label><x-marketing.editor path="data.translations.{{ $l }}.description" :value="$data['translations'][$l]['description'] ?? ''"/>
<label class="block mb-2 font-medium">{{ __('marketing.terms') }}</label><x-marketing.editor path="data.translations.{{ $l }}.terms" :value="$data['translations'][$l]['terms'] ?? ''"/>
</section>@endforeach
</div></div>
<div class="box"><div class="box-header"><h2 class="box-title">{{ __('marketing.rules') }}</h2></div><div class="box-body grid grid-cols-1 md:grid-cols-3 gap-4">
<label class="block mb-2 font-medium">{{ __('marketing.claim_until') }}<input class="form-control mt-2" type="datetime-local" wire:model="data.claim_until"></label>
<label class="block mb-2 font-medium">{{ __('marketing.valid_from') }}<input class="form-control mt-2" type="date" wire:model="data.valid_from"></label><label class="block mb-2 font-medium">{{ __('marketing.valid_until') }}<input class="form-control mt-2" type="date" wire:model="data.valid_until"></label>
<div class="md:col-span-3"><span>{{ __('marketing.weekdays') }}</span><div class="flex flex-wrap gap-3 mt-2">@foreach(['mon','tue','wed','thu','fri','sat','sun'] as $i=>$day)<label class="block mb-2 font-medium"><input type="checkbox" class="form-check-input" value="{{ $i+1 }}" wire:model="data.rules.weekdays"> {{ __('marketing.'.$day) }}</label>@endforeach</div><small>{{ __('marketing.all_days') }}</small></div>
<label class="block mb-2 font-medium">{{ __('marketing.min_pax') }}<input class="form-control mt-2" type="number" min="1" wire:model="data.rules.min_pax"></label><label class="block mb-2 font-medium">{{ __('marketing.max_pax') }}<input class="form-control mt-2" type="number" min="1" wire:model="data.rules.max_pax"></label>
<label class="block mb-2 font-medium">{{ __('marketing.usage_mode') }}<select class="form-control mt-2" wire:model.live="usageMode"><option value="single">{{ __('marketing.single') }}</option><option value="limited">{{ __('marketing.limited') }}</option><option value="unlimited">{{ __('marketing.unlimited') }}</option></select></label>
@if($usageMode==='limited')<label class="block mb-2 font-medium">{{ __('marketing.max_uses') }}<input class="form-control mt-2" type="number" min="1" wire:model="data.rules.max_uses"></label>@endif
<label class="block mb-2 font-medium">{{ __('marketing.max_assignments') }}<input class="form-control mt-2" type="number" min="1" wire:model="data.max_assignments"><small>{{ __('marketing.blank_unlimited') }}</small></label>
<label class="block mb-2 font-medium">{{ __('marketing.max_total_uses') }}<input class="form-control mt-2" type="number" min="1" wire:model="data.max_total_uses"><small>{{ __('marketing.blank_unlimited') }}</small></label>
</div></div>
<div class="box"><div class="box-header"><h2 class="box-title">{{ __('marketing.benefit') }}</h2></div><div class="box-body grid grid-cols-1 md:grid-cols-3 gap-4">
<label class="block mb-2 font-medium">{{ __('marketing.type') }}<select class="form-control mt-2" wire:model.live="data.benefit.type">@foreach(['gift','percent','amount','fixed_price'] as $type)<option value="{{ $type }}">{{ __('marketing.'.$type) }}</option>@endforeach</select></label>
@if($data['benefit']['type']!=='gift')<label class="block mb-2 font-medium">{{ $data['benefit']['type']==='percent' ? __('marketing.percent_value') : __('marketing.amount_value') }}<input class="form-control mt-2" wire:model="benefitValue" inputmode="decimal" type="number" min="0" step="0.01"></label>@endif
@if($data['benefit']['type']==='fixed_price')<label class="block mb-2 font-medium">{{ __('marketing.price_basis') }}<select class="form-control mt-2" wire:model="data.benefit.basis">@foreach(['package','person','item'] as $basis)<option value="{{ $basis }}">{{ __('marketing.'.$basis) }}</option>@endforeach</select></label>@endif
@if($data['benefit']['type']==='gift')<label class="block mb-2 font-medium">{{ __('marketing.quantity') }}<input class="form-control mt-2" type="number" min="1" wire:model="data.benefit.quantity"></label><label class="block mb-2 font-medium">{{ __('marketing.quantity_basis') }}<select class="form-control mt-2" wire:model="data.benefit.quantity_basis"><option value="fixed">{{ __('marketing.fixed_quantity') }}</option><option value="children">{{ __('marketing.per_child') }}</option></select></label>@endif
<label class="block mb-2 font-medium">{{ __('marketing.max_discount') }}<input class="form-control mt-2" type="number" min="0" step="0.01" wire:model="maxDiscount"></label>
<label class="md:col-span-3">{{ __('marketing.eligible_products') }}<select multiple class="form-control mt-2" wire:model="data.benefit.product_ids">@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->translatedName() }}</option>@endforeach</select><small>{{ __('marketing.products_help') }}</small></label>
<p class="md:col-span-3">{{ __('marketing.benefit_help') }}</p>
</div></div>
@php($baseKeys = array_column(\App\Services\Marketing\CouponService::fields(), 'key'))
<div class="box"><div class="box-header"><h2 class="box-title">{{ __('marketing.base_fields') }}</h2></div><div class="box-body">
    <p class="text-textmuted mb-4">{{ __('marketing.fields_save_help') }}</p>
    @include('livewire.marketing.coupons.partials.fields-table', ['fields' => collect($data['fields'])->filter(fn ($field) => in_array($field['key'], $baseKeys, true)), 'base' => true])
</div></div>
<div class="box"><div class="box-header"><h2 class="box-title">{{ __('marketing.custom_fields') }}</h2></div><div class="box-body">
    @include('livewire.marketing.coupons.partials.fields-table', ['fields' => collect($data['fields'])->reject(fn ($field) => in_array($field['key'], $baseKeys, true)), 'base' => false])
</div></div>
@if($editingFieldIndex !== null)
    @include('livewire.marketing.coupons.partials.field-editor', ['prefix' => 'editingField'])
@endif
@include('livewire.marketing.coupons.partials.field-editor', ['prefix' => 'newField'])
<div class="box"><div class="box-footer flex flex-wrap gap-2"><button type="submit" class="ti-btn ti-btn-success" wire:loading.attr="disabled"><i class="las la-save" aria-hidden="true"></i> {{ __('marketing.save') }}</button><a class="ti-btn ti-btn-light" href="{{ route('coupons.index') }}"><i class="las la-arrow-left" aria-hidden="true"></i> {{ __('marketing.coupons') }}</a></div></div>
</form>
@if($coupon)<div class="box mt-5"><div class="box-header"><h2 class="box-title">{{ __('marketing.assignments') }}</h2></div><div class="box-body"><input class="form-control mb-3" wire:model.live.debounce.300ms="assignmentSearch" placeholder="{{ __('marketing.search') }}"><p>{{ __('marketing.assigned') }}: {{ $coupon->assigned_count }} · {{ __('marketing.uses') }}: {{ $coupon->uses_count }}</p><div class="table-responsive"><table class="table whitespace-nowrap min-w-full"><tbody>@foreach($assignments as $a)<tr><td>{{ $a->customer->display_name }}</td><td>{{ $a->uses_count }}</td><td>{{ $a->revoked_at ? __('marketing.revoked') : __('marketing.active') }}</td><td>@unless($a->revoked_at)<button type="button" class="ti-btn ti-btn-danger" wire:click="revoke('{{ $a->id }}')" wire:confirm="{{ __('marketing.confirm_revoke') }}"><i class="las la-ban" aria-hidden="true"></i> {{ __('marketing.revoke') }}</button>@endunless</td></tr>@endforeach</tbody></table></div>{{ $assignments->links() }}</div></div>@endif
@include('marketing.editor-script')
</div></div>
