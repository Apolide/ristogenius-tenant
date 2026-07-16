<?php

namespace App\Livewire\Contacts;

use Livewire\Component;
use App\Services\Contact\ContactService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ContactShow extends Component
{
    public $contactId;
    public $contact;
    public $name;
    public $capacity;
    public $smoking_allowed;
    public $isVisible = false;

    public function render()
    {
        return view('livewire.contacts.contact-show');
    }


    #[On('click-show-contact')]
    public function showContact($id, ContactService $contactService)
    {

        $this->contact = $contactService->getContactById($id);

        $this->contactId = $this->contact->id;
        $this->name = $this->contact->name;
        $this->capacity = $this->contact->capacity;
        $this->smoking_allowed = $this->contact->smoking_allowed;
        $this->isVisible = true;
        $this->dispatch('hide-listing');

    }


    public function back(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }

   
}
