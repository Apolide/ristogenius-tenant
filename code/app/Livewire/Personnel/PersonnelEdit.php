<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use App\Services\Personnel\PersonnelService;

class PersonnelEdit extends AccountEditor
{
    public function mount(User $user, PersonnelService $personnel): void
    {
        $this->loadAccount($user, $personnel);
    }
}
