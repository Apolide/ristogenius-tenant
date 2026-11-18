<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use App\Services\Personnel\PersonnelService;

class AccountProfile extends AccountEditor
{
    protected const SELF_PROFILE = true;

    public function mount(PersonnelService $personnel): void
    {
        abort_unless(auth()->check(), 403);

        $this->loadAccount(User::query()->findOrFail(auth()->id()), $personnel);
    }
}
