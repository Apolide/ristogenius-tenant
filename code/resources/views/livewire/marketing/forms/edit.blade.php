@push('head-scripts')
    <script src="/js/tinymce/tinymce.min.js" referrerpolicy="origin"></script>
@endpush

<div class="content">
    <div class="main-content">
        <div class="flex flex-wrap justify-between gap-3 mb-6">
            <h5 class="page-title">{{ __('marketing_forms.edit') }}: {{ $form->title() }}</h5>
            <div class="flex gap-2"><a href="{{ route('marketing.forms.style', $form) }}" class="ti-btn ti-btn-primary">{{ __('Personalizza stile') }}</a><a href="{{ route('marketing.forms.index') }}" class="ti-btn ti-btn-light">{{ __('Torna alla lista') }}</a></div>
        </div>

        @if (session('success')) <div class="alert alert-success mb-5">{{ session('success') }}</div> @endif
        @error('field') <div class="alert alert-danger mb-5">{{ $message }}</div> @enderror

        @php($firstLanguage = $form->enabled_languages[0] ?? 'it')
        <form wire:submit="saveDetails" x-data="{ activeLanguage: '{{ $firstLanguage }}' }" class="box">
            <div class="box-header"><div class="box-title">{{ __('Contenuto del form') }}</div></div>
            <div class="box-body space-y-6">
                <div class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                    <div role="tablist" aria-label="{{ __('Lingue') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                        @foreach ($form->enabled_languages as $language)
                            @php($meta = $languageMeta[$language] ?? ['flag' => '🌐', 'label' => strtoupper($language)])
                            <button type="button" role="tab"
                                    :aria-selected="activeLanguage === '{{ $language }}'"
                                    @click="activeLanguage = '{{ $language }}'"
                                    class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                    :class="activeLanguage === '{{ $language }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                <span>{{ $meta['flag'] }}</span>
                                <span>{{ $meta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @foreach ($form->enabled_languages as $language)
                    <section x-show="activeLanguage === '{{ $language }}'" x-transition.opacity role="tabpanel" class="border border-defaultborder rounded p-4">
                        <div class="mb-4">
                            <label class="block mb-2 font-medium" for="title-{{ $language }}">{{ __('Titolo') }}</label>
                            <input id="title-{{ $language }}" wire:model="translations.{{ $language }}.title" class="form-control">
                            @error('translations.'.$language.'.title') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div wire:ignore>
                            <label class="block mb-2 font-medium" for="description_{{ $language }}">{{ __('Descrizione') }}</label>
                            <textarea id="description_{{ $language }}" class="form-control">{{ $translations[$language]['description'] ?? '' }}</textarea>
                        </div>
                        @error('translations.'.$language.'.description') <span class="text-danger">{{ $message }}</span> @enderror
                        @if (in_array($form->type, ['booking', 'event'], true))
                            <div wire:ignore class="mt-4">
                                <label class="block mb-2 font-medium" for="booking_policy_{{ $language }}">{{ __('Booking policy') }}</label>
                                <p class="mb-2 text-xs text-textmuted">{{ __('Questo testo verrà mostrato al cliente dopo l’invio della prenotazione.') }}</p>
                                <textarea id="booking_policy_{{ $language }}" class="form-control">{{ $translations[$language]['booking_policy'] ?? '' }}</textarea>
                            </div>
                            @error('translations.'.$language.'.booking_policy') <span class="text-danger">{{ $message }}</span> @enderror
                        @endif
                    </section>
                @endforeach
            </div>
            <div class="box-footer"><button type="submit" class="ti-btn ti-btn-success">{{ __('Salva contenuto') }}</button></div>
        </form>

        @if ($form->type === 'event')
            <form wire:submit="saveEventSchedule" class="box">
                <div class="box-header"><div class="box-title">{{ __('Disponibilità prenotazione evento') }}</div></div>
                <div class="box-body space-y-6">
                    <div>
                        <label for="event-mode" class="block mb-2 font-medium">{{ __('Date disponibili') }}</label>
                        <select id="event-mode" wire:model.live="eventSchedule.mode" class="form-control">
                            <option value="standard">{{ __('Disponibilità standard configurata nelle Impostazioni') }}</option>
                            <option value="single">{{ __('Un singolo giorno') }}</option>
                            <option value="dates">{{ __('Più giorni specifici') }}</option>
                            <option value="range">{{ __('Intervallo di giorni') }}</option>
                        </select>
                        @error('eventSchedule.mode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    @if (($eventSchedule['mode'] ?? 'single') === 'standard')
                        <div class="alert alert-info">
                            {{ __('Il form utilizzerà automaticamente i giorni di apertura, gli orari e la capienza per slot configurati nelle Impostazioni delle prenotazioni.') }}
                        </div>
                    @elseif (($eventSchedule['mode'] ?? 'single') === 'single')
                        <div>
                            <label for="event-single-date" class="block mb-2 font-medium">{{ __('Giorno dell’evento') }}</label>
                            <input id="event-single-date" type="date" wire:model="eventSchedule.single_date" min="{{ now()->toDateString() }}" class="form-control cursor-pointer dark:[color-scheme:dark]" onclick="if (this.showPicker) this.showPicker()">
                            @error('eventSchedule.single_date') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @elseif (($eventSchedule['mode'] ?? '') === 'dates')
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3"><span class="font-medium">{{ __('Giorni dell’evento') }}</span><button type="button" wire:click="addEventDate" class="ti-btn ti-btn-light ti-btn-md">{{ __('Aggiungi giorno') }}</button></div>
                            @foreach (($eventSchedule['dates'] ?? []) as $index => $date)
                                <div wire:key="event-date-{{ $index }}" class="flex items-start gap-2">
                                    <div class="grow"><input type="date" wire:model="eventSchedule.dates.{{ $index }}" min="{{ now()->toDateString() }}" class="form-control cursor-pointer dark:[color-scheme:dark]" onclick="if (this.showPicker) this.showPicker()">@error('eventSchedule.dates.'.$index)<span class="text-danger">{{ $message }}</span>@enderror</div>
                                    <button type="button" wire:click="removeEventDate({{ $index }})" class="ti-btn ti-btn-danger ti-btn-md" aria-label="{{ __('Rimuovi giorno') }}">{{ __('Rimuovi') }}</button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="grid gap-4 md:grid-cols-2">
                            <div><label for="event-range-start" class="block mb-2 font-medium">{{ __('Dal giorno') }}</label><input id="event-range-start" type="date" wire:model="eventSchedule.range_start" min="{{ now()->toDateString() }}" class="form-control cursor-pointer dark:[color-scheme:dark]" onclick="if (this.showPicker) this.showPicker()">@error('eventSchedule.range_start')<span class="text-danger">{{ $message }}</span>@enderror</div>
                            <div><label for="event-range-end" class="block mb-2 font-medium">{{ __('Al giorno') }}</label><input id="event-range-end" type="date" wire:model="eventSchedule.range_end" min="{{ $eventSchedule['range_start'] ?: now()->toDateString() }}" class="form-control cursor-pointer dark:[color-scheme:dark]" onclick="if (this.showPicker) this.showPicker()">@error('eventSchedule.range_end')<span class="text-danger">{{ $message }}</span>@enderror</div>
                        </div>
                    @endif

                    @if (($eventSchedule['mode'] ?? 'single') !== 'standard')
                    <div>
                        <label for="event-slot-mode" class="block mb-2 font-medium">{{ __('Gestione degli slot orari') }}</label>
                        <select id="event-slot-mode" wire:model.live="eventSchedule.slot_mode" class="form-control">
                            <option value="standard">{{ __('Usa gli slot standard configurati nelle Impostazioni') }}</option>
                            <option value="custom">{{ __('Usa slot personalizzati per questo evento') }}</option>
                        </select>
                        @error('eventSchedule.slot_mode') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    @if (($eventSchedule['slot_mode'] ?? 'standard') === 'custom')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div><label for="event-min-guests" class="block mb-2 font-medium">{{ __('Numero minimo di persone') }}</label><input id="event-min-guests" type="number" min="1" wire:model="eventSchedule.min_guests" class="form-control">@error('eventSchedule.min_guests')<span class="text-danger">{{ $message }}</span>@enderror</div>
                        <div><label for="event-max-guests" class="block mb-2 font-medium">{{ __('Numero massimo di persone per prenotazione') }}</label><input id="event-max-guests" type="number" min="1" wire:model="eventSchedule.max_guests" class="form-control">@error('eventSchedule.max_guests')<span class="text-danger">{{ $message }}</span>@enderror</div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3"><div><div class="font-medium">{{ __('Slot orari') }}</div><p class="text-xs text-textmuted">{{ __('La capienza indica il totale di persone prenotabili nello slot.') }}</p></div><button type="button" wire:click="addEventSlot" class="ti-btn ti-btn-light ti-btn-md">{{ __('Aggiungi slot') }}</button></div>
                        @foreach (($eventSchedule['slots'] ?? []) as $index => $slot)
                            <div wire:key="event-slot-{{ $index }}" class="grid items-start gap-3 rounded border border-defaultborder p-3 sm:grid-cols-[1fr_1fr_auto]">
                                <div><label class="block mb-2 text-sm">{{ __('Orario') }}</label><input type="time" wire:model="eventSchedule.slots.{{ $index }}.time" class="form-control cursor-pointer dark:[color-scheme:dark]" onclick="if (this.showPicker) this.showPicker()">@error('eventSchedule.slots.'.$index.'.time')<span class="text-danger">{{ $message }}</span>@enderror</div>
                                <div><label class="block mb-2 text-sm">{{ __('Capienza persone') }}</label><input type="number" min="1" wire:model="eventSchedule.slots.{{ $index }}.capacity" class="form-control">@error('eventSchedule.slots.'.$index.'.capacity')<span class="text-danger">{{ $message }}</span>@enderror</div>
                                <button type="button" wire:click="removeEventSlot({{ $index }})" class="ti-btn ti-btn-danger ti-btn-md sm:mt-7" aria-label="{{ __('Rimuovi slot') }}">{{ __('Rimuovi') }}</button>
                            </div>
                        @endforeach
                        @error('eventSchedule.slots') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    @else
                        <div class="alert alert-info">{{ __('Per le date selezionate verranno utilizzati gli orari e la capienza per slot configurati nelle Impostazioni delle prenotazioni.') }}</div>
                    @endif
                    @endif
                </div>
                <div class="box-footer"><button type="submit" class="ti-btn ti-btn-success">{{ __('Salva disponibilità evento') }}</button></div>
            </form>
        @endif

        <div class="box">
            <div class="box-header"><div class="box-title">{{ __('Campi Base') }}</div></div>
            <div class="box-body">
                @include('livewire.marketing.forms.partials.fields-table', ['fields' => $baseFields, 'emptyMessage' => __('Nessun campo base.'), 'allowEdit' => false])
            </div>
        </div>

        <div class="box">
            <div class="box-header"><div class="box-title">{{ __('Campi Personalizzati') }}</div></div>
            <div class="box-body">
                @include('livewire.marketing.forms.partials.fields-table', ['fields' => $customFields, 'emptyMessage' => __('Nessun campo personalizzato.'), 'allowEdit' => true])
            </div>
        </div>

        @if ($editingFieldId)
            <form wire:submit="updateField" x-data="{ activeEditLanguage: '{{ $firstLanguage }}' }" class="box">
                <div class="box-header"><div class="box-title">{{ __('Modifica campo personalizzato:') }} {{ $editField['key'] }}</div></div>
                <div class="box-body grid md:grid-cols-2 gap-4">
                    <div><label>{{ __('Identificativo') }}</label><input value="{{ $editField['key'] }}" class="form-control bg-gray-100" disabled><p class="text-xs text-textmuted mt-1">{{ __('L\'identificativo tecnico non può essere modificato.') }}</p></div>
                    <div><label>{{ __('Tipo') }}</label><select wire:model.live="editField.type" class="form-control">@foreach(config('marketing_forms.field_types') as $type)<option>{{ $type }}</option>@endforeach</select></div>

                    <div class="md:col-span-2 -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                        <div role="tablist" aria-label="{{ __('Lingue modifica etichetta') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                            @foreach ($form->enabled_languages as $language)
                                @php($meta = $languageMeta[$language] ?? ['flag' => '🌐', 'label' => strtoupper($language)])
                                <button type="button" role="tab" :aria-selected="activeEditLanguage === '{{ $language }}'" @click="activeEditLanguage = '{{ $language }}'"
                                        class="ti-btn !mb-0 inline-flex items-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold"
                                        :class="activeEditLanguage === '{{ $language }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                    <span>{{ $meta['flag'] }}</span><span>{{ $meta['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @foreach ($form->enabled_languages as $language)
                        <div x-show="activeEditLanguage === '{{ $language }}'" role="tabpanel" class="md:col-span-2 space-y-4">
                            <div><label>{{ __('Etichetta') }} {{ $languageMeta[$language]['label'] ?? strtoupper($language) }}</label><input wire:model="editField.label.{{ $language }}" class="form-control">@error('editField.label.'.$language)<span class="text-danger">{{ $message }}</span>@enderror</div>
                            @if (in_array($editField['type'] ?? '', ['checkbox', 'radio', 'select'], true))
                                <div><label>{{ __('Valori') }} {{ $languageMeta[$language]['label'] ?? strtoupper($language) }}</label><input wire:model="editField.options.{{ $language }}" class="form-control" placeholder="{{ __('Valore 1, Valore 2') }}"><p class="text-xs text-textmuted mt-1">{{ __('Separa i valori con una virgola.') }}</p>@error('editField.options.'.$language)<span class="text-danger">{{ $message }}</span>@enderror</div>
                            @endif
                        </div>
                    @endforeach

                    <div class="md:col-span-2 flex flex-wrap gap-6"><label><input type="checkbox" wire:model="editField.required" class="ti-switch"> {{ __('Obbligatorio') }}</label><label><input type="checkbox" wire:model="editField.visible" class="ti-switch"> {{ __('Visibile') }}</label></div>
                </div>
                <div class="box-footer"><button class="ti-btn ti-btn-success">{{ __('Salva modifiche') }}</button><button type="button" wire:click="cancelEditingField" class="ti-btn ti-btn-light">{{ __('Annulla') }}</button></div>
            </form>
        @endif

        <form wire:submit="addField" x-data="{ activeFieldLanguage: '{{ $firstLanguage }}' }" class="box">
            <div class="box-header"><div class="box-title">{{ __('Aggiungi campo personalizzato') }}</div></div>
            <div class="box-body grid md:grid-cols-2 gap-4">
                <div><label>{{ __('Identificativo') }}</label><input wire:model="newField.key" class="form-control">@error('newField.key')<span class="text-danger">{{ $message }}</span>@enderror</div>
                <div><label>{{ __('Tipo') }}</label><select wire:model.live="newField.type" class="form-control">@foreach(config('marketing_forms.field_types') as $type)<option>{{ $type }}</option>@endforeach</select></div>
                <div class="md:col-span-2 -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
                    <div role="tablist" aria-label="{{ __('Lingue etichetta') }}" class="--prevent-on-load-init flex min-w-max gap-2 rounded-xl border border-defaultborder bg-defaultbackground p-1">
                        @foreach ($form->enabled_languages as $language)
                            @php($meta = $languageMeta[$language] ?? ['flag' => '🌐', 'label' => strtoupper($language)])
                            <button type="button" role="tab"
                                    :aria-selected="activeFieldLanguage === '{{ $language }}'"
                                    @click="activeFieldLanguage = '{{ $language }}'"
                                    class="ti-btn !mb-0 inline-flex items-center justify-center gap-2 rounded-lg !px-4 !py-2 text-sm font-semibold transition"
                                    :class="activeFieldLanguage === '{{ $language }}' ? 'ti-btn-primary-full shadow-sm' : 'ti-btn-light text-defaulttextcolor'">
                                <span>{{ $meta['flag'] }}</span>
                                <span>{{ $meta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                @foreach ($form->enabled_languages as $language)
                    <div x-show="activeFieldLanguage === '{{ $language }}'" x-transition.opacity role="tabpanel" class="md:col-span-2">
                        <label for="new-field-label-{{ $language }}">{{ __('Etichetta') }} {{ $languageMeta[$language]['label'] ?? strtoupper($language) }}</label>
                        <input id="new-field-label-{{ $language }}" wire:model="newField.label.{{ $language }}" class="form-control">
                        @error('newField.label.'.$language) <span class="text-danger">{{ $message }}</span> @enderror
                        @if (in_array($newField['type'] ?? '', ['checkbox', 'radio', 'select'], true))
                            <div class="mt-4">
                                <label for="new-field-options-{{ $language }}">{{ __('Valori') }} {{ $languageMeta[$language]['label'] ?? strtoupper($language) }}</label>
                                <input id="new-field-options-{{ $language }}" wire:model="newField.options.{{ $language }}" class="form-control" placeholder="{{ __('Valore 1, Valore 2') }}">
                                <p class="text-xs text-textmuted mt-1">{{ __('Separa i valori con una virgola.') }}</p>
                                @error('newField.options.'.$language) <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                @endforeach
                <div><label class="block"><input type="checkbox" wire:model="newField.required" class="ti-switch"> {{ __('Obbligatorio') }}</label><label class="block mt-3"><input type="checkbox" wire:model="newField.visible" class="ti-switch"> {{ __('Visibile') }}</label></div>
            </div>
            <div class="box-footer"><button class="ti-btn ti-btn-success">{{ __('Aggiungi campo') }}</button></div>
        </form>
    </div>

    @script
    <script>
        if (!window.tinymce) {
            console.error('TinyMCE non caricato');
        } else {
            const languages = @js($form->enabled_languages);
            const editorFields = @js(in_array($form->type, ['booking', 'event'], true) ? ['description', 'booking_policy'] : ['description']);
            const editorTargets = {};

            languages.forEach((language) => {
                editorFields.forEach((field) => {
                    editorTargets[`${field}_${language}`] = { language, field };
                });
            });

            const syncContent = (language, field, editor) => {
                editor.save();
                $wire.set(`translations.${language}.${field}`, editor.getContent(), false);
            };

            tinymce.init({
                    license_key: 'gpl',
                    promotion: false,
                    entity_encoding: 'raw',
                    selector: Object.keys(editorTargets).map((id) => `#${id}`).join(', '),
                    force_br_newlines: true,
                    force_p_newlines: false,
                    forced_root_block: 'p',
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    height: 300,
                    menubar: true,
                    valid_elements: "ins[*],div[class|data-embed|id|itemscope|itemtype|itemprop|content|meta|style],a[href|target|rel|class|id|itemprop|style],p[itemprop|style],br,b,i,u,strong,em,li[style],ul[style],ol[style],h2[class|style],h3[itemprop|style],h4[style],img[src|alt|class|width|height|itemprop|loading],table[border|cellspacing|cellpadding|class],thead[class],tbody[class],tr[class],th[scope|class|style|width],td[class|style],span[itemprop|class|style],meta[itemprop|content],iframe[width|height|loading|src|allow|allowfullscreen]",
                    valid_styles: {
                        '*': 'color,background-color,text-align',
                    },
                    invalid_elements: 'script,style,object,embed',
                    allow_unsafe_link_target: true,
                    plugins: 'advlist anchor charmap code codesample fullscreen image insertdatetime link lists preview searchreplace table visualblocks wordcount',
                    toolbar: 'undo redo | formatselect | ' +
                        'bold italic forecolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | codesample | link image | table | code',
                    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
                    setup: function (editor) {
                        const target = editorTargets[editor.id];

                        editor.on('init', function () {
                            editor.save();
                        });

                        editor.on('change input undo redo focusout', function () {
                            syncContent(target.language, target.field, editor);
                        });
                    },
                });
        }
    </script>
    @endscript
</div>
