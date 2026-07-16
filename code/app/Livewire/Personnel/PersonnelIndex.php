<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PersonnelIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deletePersonnel(int $userId): void
    {
        abort_if($userId === auth()->id(), 422, 'Non puoi eliminare il tuo account.');

        User::query()->findOrFail($userId)->delete();
        session()->flash('success', 'Utente eliminato.');
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);
        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('roles', fn ($roles) => $roles->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('personell.index', compact('users'))->layout('components.layouts.app');
    }
}
