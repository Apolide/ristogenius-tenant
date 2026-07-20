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
use Livewire\Component;

class CustomerEdit extends Component
{
    public string $customerId;

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

    public bool $blacklisted = false;

    public function mount(string $id, CustomerService $customerService): void
    {
        $customer = $customerService->getCustomerById($id);

        $this->customerId = $customer->id;
        $this->firstname = $customer->firstname;
        $this->lastname = $customer->lastname;
        $this->email = $customer->email;
        $phone = app(PhoneNumberService::class)->split($customer->phone);
        $this->phone = $phone['national'];
        $this->phone_region = $phone['region'];
        $this->telegramid = $customer->telegramid;
        $this->region_id = $customer->region_id ?: $customer->province?->region_id;
        $this->province_id = $customer->province_id;
        $this->comuni_id = $customer->comuni_id;
        $this->birthdate = $customer->birthdate?->format('Y-m-d');
        $this->note = $customer->note;
        $this->consent_marketing = (bool) $customer->consent_marketing;
        $this->blacklisted = (bool) $customer->blacklisted;
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

    public function update(CustomerService $customerService, PhoneNumberService $phoneNumbers)
    {
        $data = $this->validate($this->rules());
        $data['phone'] = $phoneNumbers->normalize($data['phone'], $data['phone_region']);
        if (\App\Models\Customer::query()->where('phone', $data['phone'])->whereKeyNot($this->customerId)->exists()) {
            $this->addError('phone', 'Il numero di telefono è già associato a un cliente.');

            return null;
        }
        unset($data['phone_region']);
        $customerService->updateCustomer($this->customerId, $data);

        session()->flash('success', __('customers.messages.updated'));

        return redirect()->route('customers.show', $this->customerId);
    }

    public function render()
    {
        return view('livewire.customers.customer-edit', [
            'regions' => Region::query()->orderBy('name')->get(),
            'provinces' => $this->region_id
                ? Province::query()->where('region_id', $this->region_id)->orderBy('name')->get()
                : collect(),
            'comuniList' => $this->province_id
                ? Comuni::query()->where('province_id', $this->province_id)->orderBy('name')->get()
                : collect(),
            'countries' => app(PhoneCountryService::class)->countries(),
        ])->title(__('customers.form.edit'));
    }

    private function rules(): array
    {
        return [
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($this->customerId)],
            'phone' => ['required', 'string', 'max:25', new ValidPhoneNumber($this->phone_region)],
            'phone_region' => ['required', Rule::in(array_column(app(PhoneCountryService::class)->countries(), 'region'))],
            'telegramid' => ['nullable', 'string', 'max:255'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'province_id' => ['required', 'integer', Rule::exists('provinces', 'id')->where('region_id', $this->region_id)],
            'comuni_id' => ['required', 'integer', Rule::exists('comunis', 'id')->where('province_id', $this->province_id)],
            'birthdate' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
            'consent_marketing' => ['boolean'],
            'blacklisted' => ['boolean'],
        ];
    }
}
