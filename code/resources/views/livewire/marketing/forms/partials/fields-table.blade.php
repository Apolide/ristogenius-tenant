<div class="table-responsive">
    <table class="table table-bordered table-fixed min-w-full">
        <colgroup><col class="w-[34%]"><col class="w-[16%]"><col class="w-[18%]"><col class="w-[18%]"><col class="w-[14%]"></colgroup>
        <thead><tr><th>Campo</th><th>Tipo</th><th>Obbligatorio</th><th>Visibile</th><th>Azioni</th></tr></thead>
        <tbody>
            @forelse ($fields as $field)
                <tr>
                    <td class="whitespace-normal break-words">{{ $field->label[$form->enabled_languages[0]] ?? $field->key }}</td>
                    <td>{{ $field->type }}</td>
                    <td>
                        @if ($field->locked)
                            <span class="badge bg-success text-white">Obbligatorio</span>
                        @else
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="ti-switch" wire:click="toggle({{ $field->id }}, 'required')" @checked($field->required)>
                                <span class="badge {{ $field->required ? 'bg-success text-white' : 'bg-light text-defaulttextcolor' }}">{{ $field->required ? 'Obbligatorio' : 'Facoltativo' }}</span>
                            </label>
                        @endif
                    </td>
                    <td>
                        @if ($field->locked)
                            <span class="badge bg-primary text-white">Sempre visibile</span>
                        @else
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="ti-switch" wire:click="toggle({{ $field->id }}, 'visible')" @checked($field->visible)>
                                <span class="badge {{ $field->visible ? 'bg-primary text-white' : 'bg-light text-defaulttextcolor' }}">{{ $field->visible ? 'Visibile' : 'Nascosto' }}</span>
                            </label>
                        @endif
                    </td>
                    <td>
                        <div class="flex flex-wrap gap-2">
                            @if ($allowEdit ?? false)
                                <button wire:click="startEditingField({{ $field->id }})" class="ti-btn ti-btn-warning ti-btn-md">Modifica</button>
                            @endif
                            @if (! $field->required)
                                <button wire:click="deleteField({{ $field->id }})" wire:confirm="Eliminare questo campo?" class="ti-btn ti-btn-danger ti-btn-md">Elimina</button>
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
