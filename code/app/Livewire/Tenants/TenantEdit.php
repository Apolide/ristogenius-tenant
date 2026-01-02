<?php

namespace App\Livewire\Tenants;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Tenant\TenantService;
use App\Models\Region;
use App\Models\Province;
use App\Models\Comuni;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class TenantEdit extends Component
{
    public $tenantId;
    public $name;
    public $system_name;
    public $email;
    public $phone;
    public $company_name;
    public $piva;
    public $riferimento_mandato;
    
    public $regions = [];
    public $provinces = [];
    public $comunis = [];
    public $region_id;
    public $province_id;
    public $comuni_id;
    public $address;
    public $legal_officer;
    public $legal_address;
    public $legal_phone;
    public $legal_email;
    public $isVisible = false;

    
    public function mount(){
        $this->regions = Region::all()->toArray();
    }

    public function render()
    {
        return view('livewire.tenants.tenant-edit');
    }

     
    #[On('click-edit-tenant')]
    public function editProduct($id, TenantService $tenantService)
    {

        $tenant = $tenantService->getTenantById($id);

        $this->tenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->system_name = $tenant->system_name;
        $this->email = $tenant->email;
        $this->phone = $tenant->phone;
        $this->company_name = $tenant->company_name;
        $this->piva = $tenant->piva;
        $this->riferimento_mandato = $tenant->riferimento_mandato;
        
        $this->region_id = $tenant->region_id;
        $this->province_id = $tenant->province_id;
        $this->comuni_id = $tenant->comuni_id;
        $this->address = $tenant->address;
        $this->legal_officer = $tenant->legal_officer;
        $this->legal_address = $tenant->legal_address;
        $this->legal_phone = $tenant->legal_phone;
        $this->legal_email = $tenant->legal_email;

        $this->isVisible = true;
        $this->dispatch('hide-listing');
       
 
    }
    

  
    public function update(TenantService $tenantService)
    {

    
        $data = $this->validate([
            'tenantId' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'piva' => 'required|string|max:255',
            'riferimento_mandato' => 'sometimes|string|max:35',
            'region_id' => 'required|integer',
            'province_id' => 'required|integer',
            'comuni_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'legal_officer' => 'required|string|max:255',
            'legal_address' => 'required|string|max:255',
            'legal_phone' => 'required|string|max:255',
            'legal_email' => 'required|email|max:255',
        ]);

        $tenant = $tenantService->getTenantById($data["tenantId"]);

        if ($tenant->riferimento_mandato){
            $data["riferimento_mandato"] = $tenant->riferimento_mandato;
        }

        $tenantService->updateTenant($data["tenantId"], $data);
        

        
        session()->flash('message', 'Tenant aggiornato con successo.');
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('tenant-refresh');
 
    }

    public function updatedRegionId($regionId)
    {
       
        $this->province_id = null;
        $this->provinces = [];

       
        $this->comuni_id = null;
        $this->comunis = [];
        
        $this->region_id = $regionId;
     
        if ($regionId) {
            $this->provinces = Province::where('region_id', $regionId)->get()->toArray();
        }
    }



    public function updatedProvinceId($provinceId)
    {
        $this->province_id = $provinceId;
 
        $this->comunis = [];

        if ($provinceId) {
            $this->comunis = Comuni::where('province_id', $provinceId)->get()->toArray();
        }
    }

    public function generateRandomString(){

        $tenantService = new TenantService;
        $this->riferimento_mandato = $tenantService->generateRandomString();
    }


    public function abort(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

}
