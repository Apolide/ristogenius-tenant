<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Services\CustomerLanguageService;
use Livewire\Component;
use Livewire\WithPagination;

class FormIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function delete(string $id): void
    {
        MarketingForm::findOrFail($id)->delete();
        session()->flash('success', __('marketing_forms.deleted'));
    }

    public function render(CustomerLanguageService $languages)
    {
        return view('livewire.marketing.forms.index', [
            'forms' => MarketingForm::query()->when($this->search, fn ($q) => $q->where('translations', 'like', '%'.$this->search.'%'))->latest()->paginate(15),
            'languageMeta' => $languages->enabled(),
        ])->title(__('marketing_forms.title'));
    }
}
