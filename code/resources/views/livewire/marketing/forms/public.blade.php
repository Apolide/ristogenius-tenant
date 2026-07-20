<div>
    <header class="box mb-6">
        <div class="box-body flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @if ($branding['logo_url'])
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $branding['name'] }}" class="max-h-16 max-w-52">
                @endif
                <span class="text-xl font-semibold">{{ $branding['name'] }}</span>
            </div>
            @if (count($languages) > 1)
                <div>
                    <label for="form-language" class="sr-only">{{ __('public_bookings.language') }}</label>
                    <select
                        id="form-language"
                        onchange="window.location.href = this.value"
                        class="appearance-none rounded-lg border border-gray-300 bg-white px-5 py-2 pr-8 text-sm focus:border-gray-500 focus:outline-none"
                    >
                        @foreach ($languages as $code => $meta)
                            <option
                                value="{{ route('marketing.forms.public', ['language' => $code, 'form' => $form->slug]) }}"
                                @selected($language === $code)
                            >
                                {{ $meta['flag'] }} {{ strtoupper($code) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </header>

    <section class="box">
        @if ($form->image_path)
            <img src="{{ Storage::disk('public')->url($form->image_path) }}" class="w-full max-h-80 object-cover" alt="{{ $form->translations[$language]['title'] }}">
        @endif
        <div class="box-body">
            <h1 class="text-2xl font-semibold">{{ $form->translations[$language]['title'] }}</h1>
            <div class="my-4">{!! $form->translations[$language]['description'] ?? '' !!}</div>

            @if ($submitted)
                <div class="alert alert-success">Grazie, la richiesta è stata inviata correttamente.</div>
                @if (in_array($form->type, ['booking', 'event'], true) && filled($form->translations[$language]['booking_policy'] ?? null))
                    <div class="mt-4 rounded border border-defaultborder bg-defaultbackground p-4">
                        {!! $form->translations[$language]['booking_policy'] !!}
                    </div>
                @endif
            @else
                <form wire:submit="submit" class="space-y-5">
                    @foreach ($fields as $field)
                        @php
                            $fieldId = 'form-field-'.$field->id;
                        @endphp
                        <div>
                            <label for="{{ $fieldId }}" class="block mb-2 font-medium">
                                {{ $field->label[$language] ?? $field->key }}
                                @if ($field->required) <span class="text-danger" aria-hidden="true">*</span> @endif
                            </label>

                            @if ($field->type === 'textarea')
                                <textarea id="{{ $fieldId }}" wire:model="answers.{{ $field->key }}" class="form-control" @required($field->required)></textarea>
                            @elseif ($field->type === 'select')
                                <select id="{{ $fieldId }}" wire:model="answers.{{ $field->key }}" class="form-control" @required($field->required)>
                                    <option value="">Seleziona</option>
                                    @foreach (($field->options[$language] ?? []) as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            @elseif ($field->type === 'radio')
                                <div id="{{ $fieldId }}" class="space-y-2">
                                    @foreach (($field->options[$language] ?? []) as $option)
                                        <label class="flex items-center gap-2"><input type="radio" wire:model="answers.{{ $field->key }}" value="{{ $option }}" @required($field->required)> {{ $option }}</label>
                                    @endforeach
                                </div>
                            @elseif ($field->type === 'checkbox' && ($field->options[$language] ?? []) !== [])
                                <div id="{{ $fieldId }}" class="space-y-2">
                                    @foreach ($field->options[$language] as $option)
                                        <label class="flex items-center gap-2"><input type="checkbox" wire:model="answers.{{ $field->key }}" value="{{ $option }}"> {{ $option }}</label>
                                    @endforeach
                                </div>
                            @elseif ($field->type === 'checkbox')
                                <input id="{{ $fieldId }}" type="checkbox" wire:model="answers.{{ $field->key }}" value="1" @required($field->required)>
                            @elseif ($field->type === 'file')
                                <input id="{{ $fieldId }}" type="file" wire:model="answers.{{ $field->key }}" class="form-control" accept=".pdf,.doc,.docx" @required($field->required)>
                            @elseif ($field->type === 'tel' && $field->key === 'phone')
                                <div class="flex gap-2">
                                    <select wire:model="answers.phone_region" class="form-control max-w-[260px]" autocomplete="tel-country-code" @required($field->required)>
                                        @foreach($countries as $country)<option value="{{ $country['region'] }}">{{ $country['flag'] }} {{ $country['name'] }} {{ $country['prefix'] }}</option>@endforeach
                                    </select>
                                    <input id="{{ $fieldId }}" type="tel" wire:model="answers.phone" class="form-control" inputmode="tel" autocomplete="tel-national" maxlength="25" @required($field->required)>
                                </div>
                                @error('answers.phone_region') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                            @elseif (in_array($form->type, ['booking', 'event'], true) && $field->key === 'time')
                                <select id="{{ $fieldId }}" wire:model="answers.time" class="form-control cursor-pointer" @required($field->required) @disabled(blank($answers['date'] ?? null))>
                                    <option value="">{{ __('bookings.form.select_time') }}</option>
                                    @foreach ($bookingSlots as $value => $slot)
                                        <option value="{{ $value }}">{{ $slot['label'] }}</option>
                                    @endforeach
                                </select>
                            @elseif (in_array($form->type, ['booking', 'event'], true) && $field->key === 'date')
                                @php
                                    $eventSchedule = $form->type === 'event' ? ($form->schedule ?? []) : [];
                                    $eventMode = $eventSchedule['mode'] ?? null;
                                    $minimumDate = $eventMode === 'single' ? ($eventSchedule['single_date'] ?? now()->toDateString()) : ($eventMode === 'range' ? ($eventSchedule['range_start'] ?? now()->toDateString()) : now()->toDateString());
                                    $maximumDate = $eventMode === 'single' ? ($eventSchedule['single_date'] ?? null) : ($eventMode === 'range' ? ($eventSchedule['range_end'] ?? null) : null);
                                    $enabledDates = match ($eventMode) {
                                        'single' => array_values(array_filter([$eventSchedule['single_date'] ?? null])),
                                        'dates' => array_values(array_filter($eventSchedule['dates'] ?? [])),
                                        'range' => filled($eventSchedule['range_start'] ?? null) && filled($eventSchedule['range_end'] ?? null)
                                            ? [['from' => $eventSchedule['range_start'], 'to' => $eventSchedule['range_end']]]
                                            : [],
                                        default => null,
                                    };
                                @endphp
                                @if (config('marketing_forms.datepicker') === 'flatpickr')
                                    @include('livewire.marketing.forms.partials.date-flatpickr')
                                @else
                                    @include('livewire.marketing.forms.partials.date-native')
                                @endif
                            @elseif (in_array($form->type, ['booking', 'event'], true) && $field->key === 'guests')
                                @php
                                    $guestSchedule = $form->schedule ?? [];
                                    $customEventSlots = $form->type === 'event'
                                        && ($guestSchedule['mode'] ?? null) !== 'standard'
                                        && ($guestSchedule['slot_mode'] ?? (array_key_exists('slots', $guestSchedule) ? 'custom' : 'standard')) === 'custom';
                                @endphp
                                <input id="{{ $fieldId }}" type="number" wire:model.live.debounce.300ms="answers.guests"
                                       class="form-control"
                                       min="{{ $customEventSlots ? max(1, (int) ($guestSchedule['min_guests'] ?? 1)) : 1 }}"
                                       @if($customEventSlots) max="{{ max(1, (int) ($guestSchedule['max_guests'] ?? 1)) }}" @endif
                                       @required($field->required)>
                            @else
                                <input id="{{ $fieldId }}"
                                       type="{{ in_array($field->type, ['email', 'number', 'date', 'time', 'tel'], true) ? $field->type : 'text' }}"
                                       wire:model="answers.{{ $field->key }}" class="form-control @if(in_array($field->type, ['date', 'time'], true)) cursor-pointer dark:[color-scheme:dark] @endif"
                                       @required($field->required)
                                       @if(in_array($field->type, ['date', 'time'], true)) onclick="if (this.showPicker) this.showPicker()" @endif
                                       @if($field->type === 'number') min="1" @endif
                                       @if($field->type === 'date' && $field->key === 'date') min="{{ now()->toDateString() }}" @endif>
                            @endif

                            @error('answers.'.$field->key) <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endforeach

                    <div class="pt-3">
                        <button class="ti-btn ti-btn-primary-full" type="submit" wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove wire:target="submit">Invia</span>
                            <span wire:loading wire:target="submit">Invio in corso…</span>
                        </button>
                    </div>
                </form>
            @endif

            @if ($fields->contains(fn ($field) => in_array($field->key, ['privacy_consent', 'marketing_consent'], true)))
                <div class="mt-6 border-t border-defaultborder pt-4 text-sm">
                    <a href="{{ route('privacy-policy', ['language' => $language]) }}"
                       target="_blank" rel="noopener noreferrer" class="text-primary underline">
                        {{ __('marketing_forms.read_privacy_policy') }}
                    </a>
                </div>
            @endif
        </div>
    </section>
</div>
