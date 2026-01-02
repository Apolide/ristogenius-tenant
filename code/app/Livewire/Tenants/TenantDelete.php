<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Tenant\TenantService;

class TenantDelete extends Component
{
    public $tenantId;
    public $isVisible = false;



    public function render()
    {
        return view('livewire.tenants.tenant-delete');
    }

    #[On('click-delete-tenant')]
    public function confirmDelete($id)
    {
        $this->tenantId = $id;
        $this->isVisible = true;
        $this->dispatch('hide-listing');
      
    }


    public function delete(TenantService $tenantService)
    {

        $tenantService->deleteTenant($this->tenantId);

        session()->flash('message', 'Tenant eliminato con successo.');
        $this->tenantId = null;
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('tenant-refresh');
    }

    public function abort(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }
}
