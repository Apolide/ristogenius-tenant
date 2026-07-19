<div class="max-w-2xl mx-auto p-6">
    @if ($form->image_path)
        <img src="{{ Storage::disk('public')->url($form->image_path) }}" class="w-full max-h-80 object-cover rounded mb-6" alt="{{ $form->translations[$language]['title'] }}">
    @endif
    <h1 class="text-2xl font-semibold">{{ $form->translations[$language]['title'] }}</h1>
    <div class="my-4">{!! $form->translations[$language]['description'] ?? '' !!}</div>

    @if ($submitted)
        <div class="alert alert-success">Grazie, la risposta è stata inviata.</div>
    @else
        <form wire:submit="submit" class="space-y-4">
            @foreach ($fields as $field)
                <div>
                    <label class="block font-medium">{{ $field->label[$language] ?? $field->key }} @if($field->required)*@endif</label>
                    @if ($field->type === 'textarea')
                        <textarea wire:model="answers.{{ $field->key }}" class="form-control"></textarea>
                    @elseif (in_array($field->type, ['select', 'radio'], true))
                        <select wire:model="answers.{{ $field->key }}" class="form-control">
                            <option value=""></option>
                            @foreach (($field->options[$language] ?? $field->options ?? []) as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($field->type === 'checkbox')
                        <input type="checkbox" wire:model="answers.{{ $field->key }}" value="1">
                    @elseif ($field->type === 'file')
                        <input type="file" wire:model="answers.{{ $field->key }}" class="form-control" accept=".pdf,.doc,.docx">
                    @else
                        <input type="{{ in_array($field->type, ['email', 'number', 'date', 'time'], true) ? $field->type : 'text' }}" wire:model="answers.{{ $field->key }}" class="form-control">
                    @endif
                    @error('answers.'.$field->key) <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            @endforeach
            <button class="ti-btn ti-btn-primary" type="submit">Invia</button>
        </form>
    @endif
</div>
