<?php

namespace App\Livewire\Profile;

use App\Models\TenantProfile;
use Illuminate\Support\Facades\URL;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileEdit extends Component
{
    use WithFileUploads;

    public ?int $profileId = null;
    public ?string $name = null;
    public ?string $company_name = null;
    public ?string $city = null;
    public ?string $province = null;
    public ?string $postcode = null;
    public ?string $address = null;
    public $image = null;
    public ?string $profileImage = null;
    public bool $isVisible = true;

    public function mount(): void
    {
        $profile = $this->profile();
        $this->fillFromProfile($profile);
    }

    public function update(): void
    {
        $validated = $this->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:10240'],
        ]);

        $profile = $this->profile();
        $profile->update([
            'company_name' => $validated['company_name'],
            'city' => $validated['city'],
            'province' => $validated['province'],
            'postcode' => $validated['postcode'],
            'address' => $validated['address'],
        ]);

        if ($this->image) {
            $profile
                ->addMedia($this->image->getRealPath())
                ->usingFileName('logo.'.$this->image->getClientOriginalExtension())
                ->toMediaCollection('logo');
        }

        $this->reset('image');
        $this->fillFromProfile($profile->refresh());

        session()->flash('success', __('tenant_profile.updated'));
    }

    public function abort()
    {
        return redirect()->route('profile.edit');
    }

    public function render()
    {
        return view('livewire.profile.profile-edit')
            ->title(__('tenant_profile.title'));
    }

    private function profile(): TenantProfile
    {
        return TenantProfile::query()->first()
            ?: TenantProfile::query()->create(['name' => config('tenant.name', config('app.name'))]);
    }

    private function fillFromProfile(TenantProfile $profile): void
    {
        $this->profileId = $profile->id;
        $this->name = $profile->name;
        $this->company_name = $profile->company_name;
        $this->city = $profile->city;
        $this->province = $profile->province;
        $this->postcode = $profile->postcode;
        $this->address = $profile->address;
        $this->profileImage = $profile->getFirstMediaUrl('logo') ?: null;

        if ($this->profileImage && ! str_starts_with($this->profileImage, 'http')) {
            $this->profileImage = URL::to($this->profileImage);
        }
    }
}
