<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingFormSubmission;
use Livewire\Component;
use Livewire\WithPagination;

class SubmissionIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        return view('livewire.marketing.forms.submissions', [
            'submissions' => MarketingFormSubmission::query()
                ->with('form')
                ->whereHas('form', fn ($query) => $query->whereNotIn('type', ['booking', 'event']))
                ->latest()
                ->paginate(15),
        ])->title(__('marketing_forms.responses'));
    }
}
