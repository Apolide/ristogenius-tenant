<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class Usercreate extends Component
{

    public $name, $email, $role, $role_list;

    public function mount(){
        $this->role_list = Role::all()->pluck('name');
        
    }
    
    public function render()
    {
        return view('livewire.users.usercreate');
    }

    public function submit()
    {
        
        $data = $this->validate([
            
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|string'
            
        ]);

        $user = new User;
        $user->name = $this->name;
        $user->email = $this->email;
        $user->password = Hash::make('1234556dummy');
        $user->save();
        $user->assignRole($this->role);

        $this->redirect('/manage/users');

        
    }
}
