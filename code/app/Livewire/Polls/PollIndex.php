<?php

namespace App\Livewire\Polls;

use App\Models\Poll;
use App\Services\Marketing\MarketingAccess;
use Livewire\Component;
use Livewire\WithPagination;

class PollIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.polls.index', [
            'polls' => Poll::where('label', 'like', '%'.trim($this->search).'%')
                ->withCount(['invitations as responses_count' => fn ($query) => $query->whereNotNull('submitted_at')])
                ->orderBy('label')->orderBy('id')->paginate(20),
        ])->title(__('polls.title'));
    }
}
