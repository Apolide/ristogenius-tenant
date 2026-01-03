<?php

namespace App\Livewire\Contacts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Contact\ContactService;
use App\Models\Region;
use App\Models\Province;
use App\Models\Comuni;
use Illuminate\Support\Facades\Auth;


class ContactCreate extends Component
{

    public $name;
    public $system_name;
    public $email;
    public $phone;
    public $company_name;
    public $piva;
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
        return view('livewire.contacts.contact-create');
    }


    #[On('click-create-contact')]
    public function createContact()
    {
        $this->dispatch('hide-listing');
        $this->isVisible = true;
        
    }


    public function save(ContactService $contactService)
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'system_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'piva' => 'required|string|max:255',
            'region_id' => 'required|integer',
            'province_id' => 'required|integer',
            'comuni_id' => 'required|integer',
            'address' => 'required|string|max:255',
            'legal_officer' => 'required|string|max:255',
            'legal_address' => 'required|string|max:255',
            'legal_phone' => 'required|string|max:255',
            'legal_email' => 'required|email|max:255',
        ]);

      
        $contactService->createContact($data);

        $this->resetFields();
        session()->flash('message', 'Contact creato con successo.');
        $this->isVisible = false;
        $this->dispatch('content-refresh');
        $this->dispatch('show-listing');
        
        
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




    public function abort(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

    private function resetFields()
    {
        $this->name = '';
        $this->system_name = ''; 
    }
}
