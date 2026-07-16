<?php

namespace App\Livewire\Personnel;

use App\Models\User;
use App\Notifications\Mail\User\EmployeeInvitation;
use App\Services\Personnel\PersonnelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PersonnelCreate extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '+39';
    public string $role = '';
    public string $lang = PersonnelService::DEFAULT_LANGUAGE;
    public bool $receive_whatsapp_notifications = false;
    public bool $receive_telegram_notifications = false;

    /** @var list<string> */
    public array $role_list = [];

    /** @var array<string, string> */
    public array $language_list = [];

    public function mount(PersonnelService $personnel): void
    {
        $this->role_list = $personnel->roles();
        $this->language_list = $personnel->languages();

        if (! array_key_exists($this->lang, $this->language_list)) {
            $this->lang = array_key_first($this->language_list) ?? PersonnelService::DEFAULT_LANGUAGE;
        }
    }

    public function submit(): void
    {
        $this->email = Str::lower(trim($this->email));
        $this->phone = preg_replace('/[\s().-]+/', '', $this->phone) ?? $this->phone;

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^\+[1-9]\d{6,14}$/'],
            'role' => ['required', Rule::in($this->role_list)],
            'lang' => ['required', Rule::in(array_keys($this->language_list))],
            'receive_whatsapp_notifications' => ['boolean'],
            'receive_telegram_notifications' => ['boolean'],
        ], [
            'phone.regex' => 'Inserisci il telefono in formato internazionale, ad esempio +393401234567.',
        ]);

        $data['name'] = trim($data['name']);
        $data['password'] = Hash::make(Str::random(64));
        $data['enabled'] = false;
        $data['invited_at'] = now();

        $user = DB::transaction(function () use ($data): User {
            $role = $data['role'];
            unset($data['role']);

            $user = User::query()->create($data);
            $user->assignRole($role);

            return $user;
        });

        $user->notify(new EmployeeInvitation(Password::broker()->createToken($user)));

        session()->flash('success', 'Dipendente creato. L’email di attivazione è stata inviata.');
        $this->redirectRoute('personnel.index');
    }

    public function render()
    {
        return view('personell.create')->layout('components.layouts.app');
    }
}
