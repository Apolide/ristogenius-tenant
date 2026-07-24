<?php

namespace App\Livewire\Customers;

use App\Models\Comuni;
use App\Models\Province;
use App\Models\Region;
use App\Rules\ValidPhoneNumber;
use App\Services\Customer\CustomerService;
use App\Services\PhoneCountryService;
use App\Services\PhoneNumberService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomerCreate extends Component
{
    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $email = null;

    public ?string $phone = null;

    public string $phone_region = 'IT';

    public ?string $telegramid = null;

    public string|int|null $region_id = null;

    public string|int|null $province_id = null;

    public string|int|null $comuni_id = null;

    public ?string $birthdate = null;

    public ?string $note = null;

    public bool $consent_marketing = false;

    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.customers.customer-create', [
            'regions' => Region::query()->orderBy('name')->get(),
            'provinces' => $this->region_id
                ? Province::query()->where('region_id', $this->region_id)->orderBy('name')->get()
                : collect(),
            'comuniList' => $this->province_id
                ? Comuni::query()->where('province_id', $this->province_id)->orderBy('name')->get()
                : collect(),
            'countries' => app(PhoneCountryService::class)->countries(),
        ]);
    }

    #[On('click-create-customer')]
    public function createCustomer(): void
    {
        $this->resetFields();
        $this->resetErrorBag();
        $this->dispatch('hide-listing');
        $this->isVisible = true;
    }

    public function updatedProvinceId(): void
    {
        $this->comuni_id = null;
    }

    public function updatedRegionId(): void
    {
        $this->province_id = null;
        $this->comuni_id = null;
    }

    public function save(CustomerService $customerService, PhoneNumberService $phoneNumbers): void
    {
        $data = $this->validate($this->rules());
        $data['phone'] = $phoneNumbers->normalize($data['phone'], $data['phone_region']);
        if (\App\Models\Customer::query()->where('phone', $data['phone'])->exists()) {
            $this->addError('phone', 'Il numero di telefono è già associato a un cliente.');

            return;
        }
        unset($data['phone_region']);
        $customerService->createCustomer($data);

        $this->resetFields();
        session()->flash('success', __('customers.messages.created'));
        $this->isVisible = false;
        $this->dispatch('customer-refresh');
        $this->dispatch('show-listing');
    }

    public function abort(): void
    {
        $this->resetFields();
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

    private function rules(): array
    {
        return [
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:25', new ValidPhoneNumber($this->phone_region)],
            'phone_region' => ['required', Rule::in(array_column(app(PhoneCountryService::class)->countries(), 'region'))],
            'telegramid' => ['nullable', 'string', 'max:255'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'province_id' => ['required', 'integer', Rule::exists('provinces', 'id')->where('region_id', $this->region_id)],
            'comuni_id' => ['required', 'integer', Rule::exists('comunis', 'id')->where('province_id', $this->province_id)],
            'birthdate' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'consent_marketing' => ['boolean'],
        ];
    }

    private function resetFields(): void
    {
        $this->firstname = null;
        $this->lastname = null;
        $this->email = null;
        $this->phone = null;
        $this->phone_region = 'IT';
        $this->telegramid = null;
        $this->region_id = null;
        $this->province_id = null;
        $this->comuni_id = null;
        $this->birthdate = null;
        $this->note = null;
        $this->consent_marketing = false;
    }
}
