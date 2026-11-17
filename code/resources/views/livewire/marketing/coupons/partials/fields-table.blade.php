<div class="table-responsive">
    <table class="table table-bordered table-fixed min-w-full">
        <colgroup><col class="w-[34%]"><col class="w-[16%]"><col class="w-[18%]"><col class="w-[18%]"><col class="w-[14%]"></colgroup>
        <thead><tr><th>{{ __('marketing.label') }}</th><th>{{ __('marketing.type') }}</th><th>{{ __('marketing.required') }}</th><th>{{ __('marketing.visible') }}</th><th>{{ __('marketing.actions') }}</th></tr></thead>
        <tbody>
            @forelse($fields as $index => $field)
                @php($locked = in_array($field['key'], ['name', 'email', 'privacy_consent'], true))
                @php($fallback = __('marketing.field_'.$field['key']))
                <tr wire:key="coupon-field-{{ $index }}">
                    <td class="whitespace-normal break-words">{{ ($field['label'][$data['enabled_languages'][0] ?? 'it'] ?? '') ?: ($fallback === 'marketing.field_'.$field['key'] ? $field['key'] : $fallback) }}<small class="block text-textmuted">{{ $field['key'] }}</small></td>
                    <td>{{ __('marketing.field_'.$field['type']) }}</td>
                    <td>
                        @if($locked)<span class="badge bg-success text-white">{{ __('marketing.required') }}</span>
                        @else<label class="inline-flex items-center gap-2"><input type="checkbox" class="ti-switch" wire:model.live="data.fields.{{ $index }}.required" @disabled($field['key'] === 'marketing_consent')><span>{{ $field['required'] ? __('marketing.required') : __('marketing.optional') }}</span></label>@endif
                    </td>
                    <td>@if($locked)<span class="badge bg-primary text-white">{{ __('marketing.always_visible') }}</span>@else<label class="inline-flex items-center gap-2"><input type="checkbox" class="ti-switch" wire:model.live="data.fields.{{ $index }}.visible"><span>{{ __('marketing.visible') }}</span></label>@endif</td>
                    <td><div class="flex flex-wrap gap-2">
                        <button type="button" class="ti-btn ti-btn-warning" wire:click="startEditingField({{ $index }})"><i class="las la-pen" aria-hidden="true"></i> {{ __('marketing.edit') }}</button>
                        @unless($base)<button type="button" class="ti-btn ti-btn-danger" wire:click="removeField({{ $index }})"><i class="las la-trash" aria-hidden="true"></i> {{ __('marketing.remove') }}</button>@endunless
                    </div></td>
                </tr>
            @empty<tr><td colspan="5" class="text-center">{{ __('marketing.empty') }}</td></tr>@endforelse
        </tbody>
    </table>
</div>
