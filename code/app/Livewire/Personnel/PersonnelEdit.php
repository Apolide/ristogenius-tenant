<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use App\Services\Personnel\PersonnelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PersonnelEdit extends Component
{
    public int $userId;
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

    public function mount(User $user, PersonnelService $personnel): void
    {
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

    public function submit(): void
    {
        $this->email = Str::lower(trim($this->email));
        $this->phone = preg_replace('/[\s().-]+/', '', $this->phone) ?? $this->phone;

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+[1-9]\d{6,14}$/'],
            'role' => ['required', Rule::in($this->role_list)],
            'lang' => ['required', Rule::in(array_keys($this->language_list))],
            'receive_whatsapp_notifications' => ['boolean'],
            'receive_telegram_notifications' => ['boolean'],
        ], [
            'phone.regex' => __('personnel.validation.phone'),
        ]);

        DB::transaction(function () use ($data): void {
            $role = $data['role'];
            unset($data['role']);
            $data['name'] = trim($data['name']);

            $user = User::query()->findOrFail($this->userId);
            $user->update($data);
            $user->syncRoles([$role]);
        });

        session()->flash('success', __('personnel.messages.updated'));
        $this->redirectRoute('personnel.index');
    }

    public function render()
    {
        return view('personell.edit')->layout('components.layouts.app');
    }
}
