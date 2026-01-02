<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;

class Userlist extends Component
{

    public $users;

    public function mount()
    {
        $this->users = User::all();
    }
    
    public function render()
    {
        $this->users = User::all();
        return view('livewire.users.userlist');
    }
}
