@php($draft = $this->{$prefix})
@php($isBase = $prefix === 'editingField' && in_array($data['fields'][$editingFieldIndex]['key'], array_column(\App\Services\Marketing\CouponService::fields(), 'key'), true))
<div class="box" x-data="{ activeFieldLanguage: '{{ $data['enabled_languages'][0] ?? 'it' }}' }">
    <div class="box-header"><h2 class="box-title">{{ $prefix === 'newField' ? __('marketing.add_custom_field') : __('marketing.edit_field') }}</h2></div>
    <div class="box-body space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label>{{ __('marketing.key') }}<input class="form-control" wire:model="{{ $prefix }}.key" @readonly($isBase)></label>
            <label>{{ __('marketing.type') }}<select class="form-control" wire:model.live="{{ $prefix }}.type" @disabled($isBase)>@foreach(['text','textarea','email','tel','date','time','checkbox','number','select','radio','province','city'] as $type)<option value="{{ $type }}">{{ __('marketing.field_'.$type) }}</option>@endforeach</select></label>
        </div>
        @error($prefix.'.key')<p class="text-danger">{{ $message }}</p>@enderror
        <nav class="flex flex-wrap gap-2" role="tablist" aria-label="{{ __('marketing.language') }}">
            @foreach($data['enabled_languages'] as $code)<button type="button" role="tab" :aria-selected="activeFieldLanguage === '{{ $code }}'" @click="activeFieldLanguage = '{{ $code }}'" class="ti-btn !mb-0" :class="activeFieldLanguage === '{{ $code }}' ? 'ti-btn-primary-full' : 'ti-btn-light'">{{ app(\App\Services\CustomerLanguageService::class)->meta($code)['flag'] }} {{ strtoupper($code) }}</button>@endforeach
        </nav>
        @foreach($data['enabled_languages'] as $code)
            <div x-show="activeFieldLanguage === '{{ $code }}'" role="tabpanel" class="space-y-3">
                <label class="block">{{ __('marketing.label') }} {{ strtoupper($code) }}<input class="form-control" wire:model="{{ $prefix }}.label.{{ $code }}"></label>
                @error($prefix.'.label.'.$code)<p class="text-danger">{{ $message }}</p>@enderror
                @if(in_array($draft['type'], ['select', 'radio'], true))
                    @foreach($draft['options'][$code] ?? [] as $option => $value)
                        <div class="flex gap-2"><input class="form-control" aria-label="{{ __('marketing.option') }} {{ strtoupper($code) }}" wire:model="{{ $prefix }}.options.{{ $code }}.{{ $option }}"><button type="button" class="ti-btn ti-btn-danger" wire:click="removeDraftOption('{{ $prefix }}', {{ $option }})"><i class="las la-trash" aria-hidden="true"></i></button></div>
                    @endforeach
                    <button type="button" class="ti-btn ti-btn-light" wire:click="addDraftOption('{{ $prefix }}')"><i class="las la-plus" aria-hidden="true"></i> {{ __('marketing.add_option') }}</button>
                @endif
            </div>
        @endforeach
        <div class="flex flex-wrap gap-4">
            <label><input type="checkbox" class="ti-switch" wire:model="{{ $prefix }}.required" @disabled(in_array($draft['key'], ['name','email','privacy_consent','marketing_consent'], true))> {{ __('marketing.required') }}</label>
            <label><input type="checkbox" class="ti-switch" wire:model="{{ $prefix }}.visible" @disabled(in_array($draft['key'], ['name','email','privacy_consent'], true))> {{ __('marketing.visible') }}</label>
        </div>
    </div>
    <div class="box-footer flex gap-2">
        <button type="button" class="ti-btn ti-btn-success" wire:click="{{ $prefix === 'newField' ? 'addField' : 'saveEditingField' }}"><i class="las la-save" aria-hidden="true"></i> {{ $prefix === 'newField' ? __('marketing.add_field') : __('marketing.save') }}</button>
        @if($prefix === 'editingField')<button type="button" class="ti-btn ti-btn-light" wire:click="cancelEditingField">{{ __('marketing.cancel') }}</button>@endif
    </div>
</div>
