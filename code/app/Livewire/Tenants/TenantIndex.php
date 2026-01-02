<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Services\Tenant\TenantService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TenantIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $isVisible = true;


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render(TenantService $tenantService)
    {
        $tenants = $tenantService->getTenants();
       
        return view('livewire.tenants.tenant-index', [
            'tenants' => $tenants,
        ]);
    }

    #[On('tenant-refresh')]
    public function refreshView(TenantService $tenantService)
    {
    
        $tenants = $tenantService->getTenants();
    

        return view('livewire.tenants.tenant-index', [
            'tenants' => $tenants,
        ]);
    }
   
    #[On('show-listing')]
    public function showListing(){
       $this->isVisible = true;
    }

    #[On('hide-listing')]
    public function hideListing(){
       $this->isVisible = false;
    }
   

  
    
}
