<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use App\Services\Personnel\PersonnelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

abstract class AccountEditor extends Component
{
    protected const SELF_PROFILE = false;

    #[Locked]
    public int $userId;

    public bool $enabled = false;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $role = '';
    public string $lang = PersonnelService::DEFAULT_LANGUAGE;
    public bool $receive_whatsapp_notifications = false;
    public bool $receive_telegram_notifications = false;

    /** @var list<string> */
    public array $role_list = [];

    /** @var array<string, string> */
    public array $language_list = [];

    protected function loadAccount(User $user, PersonnelService $personnel): void
    {
        $this->authorizeProfile();
        $this->enabled = (bool) $user->enabled;
        $this->role_list = $personnel->roles();
        $this->language_list = $personnel->languages();
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->getRoleNames()->first() ?? '';
        $this->lang = array_key_exists($user->lang, $this->language_list)
            ? $user->lang
            : (array_key_first($this->language_list) ?? PersonnelService::DEFAULT_LANGUAGE);
        $this->receive_whatsapp_notifications = $user->receive_whatsapp_notifications;
        $this->receive_telegram_notifications = $user->receive_telegram_notifications;
    }

    private function authorizeProfile(): void
    {
        abort_unless(auth()->check() && (static::SELF_PROFILE || auth()->user()->hasRole('admin')), 403);
    }

    public function submit(PersonnelService $personnel): void
    {
        $this->authorizeProfile();
        $admin = auth()->user()->hasRole('admin');
        $user = static::SELF_PROFILE ? auth()->user() : User::query()->findOrFail($this->userId);
        $this->name = trim($this->name);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'lang' => ['required', Rule::in(array_keys($personnel->languages()))],
        ];

        if ($admin) {
            $this->email = Str::lower(trim($this->email));
            $this->phone = preg_replace('/[\s().-]+/', '', $this->phone) ?? $this->phone;
            $rules += [
                'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'phone' => [static::SELF_PROFILE ? 'nullable' : 'required', 'string', 'max:32', 'regex:/^\+[1-9]\d{6,14}$/'],
                'role' => ['required', Rule::in($personnel->roles())],
                'receive_whatsapp_notifications' => ['boolean'],
                'receive_telegram_notifications' => ['boolean'],
            ];

            if (static::SELF_PROFILE) {
                $rules['enabled'] = ['boolean'];
            }
        }

        $data = $this->validate($rules, ['phone.regex' => __('personnel.validation.phone')]);

        DB::transaction(function () use ($user, $data): void {
            $role = $data['role'] ?? null;
            unset($data['role']);
            $user->update($data);

            if ($role !== null && (! static::SELF_PROFILE || $role !== $user->getRoleNames()->first())) {
                $user->syncRoles([$role]);
            }
        });

        session()->flash('success', __(static::SELF_PROFILE ? 'interface.profile_saved' : 'personnel.messages.updated'));
        $this->redirectRoute(static::SELF_PROFILE ? 'account.profile' : 'personnel.index');
    }

    public function render()
    {
        $this->authorizeProfile();

        return view('personell.edit', [
            'selfProfile' => static::SELF_PROFILE,
            'canEditAccountFields' => auth()->user()->hasRole('admin'),
            'account' => static::SELF_PROFILE ? auth()->user() : null,
        ])->layout('components.layouts.app');
    }
}
