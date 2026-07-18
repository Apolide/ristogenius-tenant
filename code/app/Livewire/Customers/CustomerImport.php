<?php

namespace App\Livewire\Customers;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CustomerImport extends Component
{
    use WithFileUploads;

    public $file = null;
    public bool $isVisible = false;

    public function render()
    {
        return view('livewire.customers.customer-import');
    }

    #[On('click-import-customer')]
    public function showImport(): void
    {
        $this->resetErrorBag();
        $this->dispatch('hide-listing');
        $this->isVisible = true;
    }

    public function import(): void
    {
        session()->flash('success', __('customers.messages.import_pending'));
    }

    public function abort(): void
    {
        $this->file = null;
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }
}
