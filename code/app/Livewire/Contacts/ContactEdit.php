<?php

namespace App\Livewire\Contacts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Contact\ContactService;
use App\Models\Region;
use App\Models\Province;
use App\Models\Comuni;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ContactEdit extends Component
{
    public $contactId;
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
        return view('livewire.contacts.contact-edit');
    }

     
    #[On('click-edit-contact')]
    public function editProduct($id, ContactService $contactService)
    {

        $contact = $contactService->getContactById($id);

        $this->contactId = $contact->id;
        $this->name = $contact->name;
        $this->system_name = $contact->system_name;
        $this->email = $contact->email;
        $this->phone = $contact->phone;
        $this->company_name = $contact->company_name;
        $this->piva = $contact->piva;
        $this->riferimento_mandato = $contact->riferimento_mandato;
        
        $this->region_id = $contact->region_id;
        $this->province_id = $contact->province_id;
        $this->comuni_id = $contact->comuni_id;
        $this->address = $contact->address;
        $this->legal_officer = $contact->legal_officer;
        $this->legal_address = $contact->legal_address;
        $this->legal_phone = $contact->legal_phone;
        $this->legal_email = $contact->legal_email;

        $this->isVisible = true;
        $this->dispatch('hide-listing');
       
 
    }
    

  
    public function update(ContactService $contactService)
    {

    
        $data = $this->validate([
            'contactId' => 'required|string|max:255',
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

        $contact = $contactService->getContactById($data["contactId"]);

        if ($contact->riferimento_mandato){
            $data["riferimento_mandato"] = $contact->riferimento_mandato;
        }

        $contactService->updateContact($data["contactId"], $data);
        

        
        session()->flash('message', 'Contact aggiornato con successo.');
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('content-refresh');
 
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

        $contactService = new ContactService;
        $this->riferimento_mandato = $contactService->generateRandomString();
    }


    public function abort(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

}
