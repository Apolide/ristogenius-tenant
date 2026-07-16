<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Livewire\Component;

class CustomerShow extends Component
{
    public Customer $customer;

    public function mount(string $id, CustomerService $customerService): void
    {
        $this->customer = $customerService->getCustomerById($id);
    }

    public function render()
    {
        return view('livewire.customers.customer-show')->title('Scheda cliente');
    }
}
