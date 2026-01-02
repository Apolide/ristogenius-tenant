<?php

namespace App\Livewire\Users;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class Useredit extends Component
{

    public $model, $name, $email, $role, $role_list;

    public function mount($id){
        $this->model = User::findOrFail($id);
        $this->name = $this->model->name;
        $this->email = $this->model->email;
        $this->role = $this->model->getRoleNames()[0];
        $this->role_list = Role::all()->pluck('name');
        
    }
    
    public function render()
    {
        return view('livewire.users.useredit');
    }

    public function submit()
    {
        
        $data = $this->validate([
            
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required|string'
            
        ]);

        $user = User::findOrFail($this->model->id);
        $user->name = $this->name;
        $user->email = $this->email;
        $user->update();
        $user->syncRoles([$this->role]);

        $this->redirect('/manage/users');

        
    }
}
