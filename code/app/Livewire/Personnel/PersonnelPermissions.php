<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Livewire\Component;

class PersonnelPermissions extends Component
{
    public string $search = '';

    /** @var array<int, array<string, bool>> */
    public array $assigned = [];

    public function mount(PersonnelPermissionsService $permissions): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        $permissions->ensurePermissionsExist();
        $this->loadAssignments();
    }

    public function updatedSearch(): void
    {
        $this->loadAssignments();
    }

    public function toggle(
        int $userId,
        string $permission,
        PersonnelPermissionsService $permissions,
    ): void {
        abort_unless(auth()->user()?->hasRole('admin'), 403);

        $user = User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'admin'))
            ->findOrFail($userId);

        $enabled = (bool) ($this->assigned[$userId][$permission] ?? false);
        $permissions->set($user, $permission, $enabled);

        session()->flash('success', 'Permesso aggiornato.');
    }

    public function render(PersonnelPermissionsService $permissions)
    {
        $users = $this->users()->get();

        return view('personell.permissions', [
            'users' => $users,
            'features' => $permissions->features(),
        ])->layout('components.layouts.app');
    }

    private function loadAssignments(): void
    {
        $this->assigned = $this->users()
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => collect(array_keys(PersonnelPermissionsService::FEATURES))
                    ->mapWithKeys(fn (string $permission): array => [
                        $permission => $user->hasDirectPermission($permission),
                    ])
                    ->all(),
            ])
            ->all();
    }

    private function users()
    {
        $search = trim($this->search);

        return User::query()
            ->with(['roles', 'permissions'])
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'admin'))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name');
    }
}
