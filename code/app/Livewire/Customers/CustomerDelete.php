<?php

namespace App\Livewire\Customers;

use App\Services\Customer\CustomerService;
use Livewire\Attributes\On;
use Livewire\Component;

class CustomerDelete extends Component
{
    public ?string $customerId = null;
    public ?string $customerName = null;
    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.customers.customer-delete');
    }

    #[On('click-delete-customer')]
    public function confirmDelete(string $id, CustomerService $customerService): void
    {
        $customer = $customerService->getCustomerById($id);

        $this->customerId = $customer->id;
        $this->customerName = $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname) ?: $customer->phone;
        $this->isVisible = true;
        $this->dispatch('hide-listing');
    }

    public function delete(CustomerService $customerService): void
    {
        if ($this->customerId) {
            $customerService->deleteCustomer($this->customerId);
        }

        session()->flash('success', 'Cliente eliminato con successo.');
        $this->customerId = null;
        $this->customerName = null;
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('customer-refresh');
    }

    public function abort(): void
    {
        $this->customerId = null;
        $this->customerName = null;
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }
}
