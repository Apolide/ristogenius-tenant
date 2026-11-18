<div class="table-responsive">
    <table class="table table-bordered table-fixed min-w-full">
        <colgroup><col class="w-[34%]"><col class="w-[16%]"><col class="w-[18%]"><col class="w-[18%]"><col class="w-[14%]"></colgroup>
        <thead><tr><th>{{ __('marketing_forms.field') }}</th><th>{{ __('marketing_forms.type') }}</th><th>{{ __('marketing_forms.required') }}</th><th>{{ __('marketing_forms.visible') }}</th><th>{{ __('marketing_forms.actions') }}</th></tr></thead>
        <tbody>
            @forelse ($fields as $field)
                <tr>
                    <td class="whitespace-normal break-words">{{ $field->label[$form->enabled_languages[0]] ?? $field->key }}</td>
                    <td>{{ $field->type }}</td>
                    <td>
                        @if ($field->locked)
                            <span class="badge bg-success text-white">{{ __('marketing_forms.required') }}</span>
                        @else
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="ti-switch" wire:click="toggle({{ $field->id }}, 'required')" @checked($field->required)>
                                <span class="badge {{ $field->required ? 'bg-success text-white' : 'bg-light text-defaulttextcolor' }}">{{ $field->required ? __('marketing_forms.required') : __('marketing_forms.optional') }}</span>
                            </label>
                        @endif
                    </td>
                    <td>
                        @if ($field->locked)
                            <span class="badge bg-primary text-white">{{ __('marketing_forms.always_visible') }}</span>
                        @else
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="ti-switch" wire:click="toggle({{ $field->id }}, 'visible')" @checked($field->visible)>
                                <span class="badge {{ $field->visible ? 'bg-primary text-white' : 'bg-light text-defaulttextcolor' }}">{{ $field->visible ? __('marketing_forms.visible') : __('marketing_forms.hidden') }}</span>
                            </label>
                        @endif
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            @if ($allowEdit ?? false)
                                <button wire:click="startEditingField({{ $field->id }})" class="ti-btn ti-btn-warning ti-btn-md">{{ __('marketing_forms.edit_action') }}</button>
                            @endif
                            @if (($allowEdit ?? false) && ! $field->required)
                                <button wire:click="deleteField({{ $field->id }})" wire:confirm="{{ __('marketing_forms.delete_this_field') }}" class="ti-btn ti-btn-danger ti-btn-md">{{ __('marketing_forms.delete') }}</button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-textmuted py-6">{{ $emptyMessage }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
