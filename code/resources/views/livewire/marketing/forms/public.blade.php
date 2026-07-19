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
                <nav class="flex flex-wrap gap-2" aria-label="Lingua">
                    @foreach ($languages as $code => $meta)
                        <a href="{{ route('marketing.forms.public', ['language' => $code, 'form' => $form->slug]) }}"
                           class="ti-btn {{ $language === $code ? 'ti-btn-primary' : 'ti-btn-light' }} ti-btn-sm" hreflang="{{ $code }}">
                            {{ $meta['flag'] }} {{ strtoupper($code) }}
                        </a>
                    @endforeach
                </nav>
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
            @else
                <form wire:submit="submit" class="space-y-5">
                    @foreach ($fields as $field)
                        @php($fieldId = 'form-field-'.$field->id)
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
                            @else
                                <input id="{{ $fieldId }}"
                                       type="{{ in_array($field->type, ['email', 'number', 'date', 'time', 'tel'], true) ? $field->type : 'text' }}"
                                       wire:model="answers.{{ $field->key }}" class="form-control"
                                       @required($field->required)
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
        </div>
    </section>
</div>
