<?php

namespace App\Livewire\Polls;

use App\Models\Poll;
use App\Services\Marketing\MarketingAccess;
use Livewire\Component;
use Livewire\WithPagination;

class PollResponses extends Component
{
    use WithPagination;

    public Poll $poll;

    public function boot(): void
    {
        MarketingAccess::authorize();
    }

    public function mount(Poll $poll): void
    {
        $this->poll = $poll;
    }

    public function render()
    {
        return view('livewire.polls.responses', [
            'responses' => $this->poll->invitations()->whereNotNull('submitted_at')->latest('submitted_at')->orderBy('id')->paginate(20),
        ])->title(__('polls.responses'));
    }
}
