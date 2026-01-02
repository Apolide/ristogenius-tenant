<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use App\Services\Tenant\TenantService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class TenantShow extends Component
{
    public $tenantId;
    public $tenant;
    public $name;
    public $capacity;
    public $smoking_allowed;
    public $isVisible = false;

    public function render()
    {
        return view('livewire.tenants.tenant-show');
    }


    #[On('click-show-tenant')]
    public function showTenant($id, TenantService $tenantService)
    {

        $this->tenant = $tenantService->getTenantById($id);

        $this->tenantId = $this->tenant->id;
        $this->name = $this->tenant->name;
        $this->capacity = $this->tenant->capacity;
        $this->smoking_allowed = $this->tenant->smoking_allowed;
        $this->isVisible = true;
        $this->dispatch('hide-listing');

    }


    public function back(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

   
}
