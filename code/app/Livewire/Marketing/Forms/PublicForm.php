<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use Livewire\Component;
use Livewire\WithFileUploads;

class PublicForm extends Component
{
    use WithFileUploads;

    public MarketingForm $form;

    public string $language;

    public array $answers = [];

    public bool $submitted = false;

    public function mount(MarketingForm $form, string $language): void
    {
        abort_unless($form->is_active && in_array($language, $form->enabled_languages, true), 404);
        $this->form = $form;
        $this->language = $language;
    }

    public function submit(): void
    {
        $rules = [];
        foreach ($this->form->fields()->where('visible', true)->get() as $f) {
            $r = $f->required ? ['required'] : ['nullable'];
            $r[] = match ($f->type) {
                'email' => 'email',
                'number' => 'numeric',
                'date' => 'date',
                'file' => 'file|mimes:pdf,doc,docx|max:10240',
                default => 'string',
            };
            $rules['answers.'.$f->key] = $r;
        }
        $payload = $this->validate($rules)['answers'];
        foreach ($this->form->fields()->where('visible', true)->where('type', 'file')->get() as $field) {
            if (isset($payload[$field->key])) {
                $payload[$field->key] = $payload[$field->key]->store("marketing-forms/{$this->form->id}", 'local');
            }
        }
        $this->form->submissions()->create(['language' => $this->language, 'payload' => $payload]);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.marketing.forms.public', ['fields' => $this->form->fields()->where('visible', true)->get()])->layout('layouts.guest');
    }
}
