<?php

namespace App\Livewire\Contacts;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Services\Contact\ContactService;

class ContactDelete extends Component
{
    public $contactId;
    public $isVisible = false;



    public function render()
    {
        return view('livewire.contacts.contact-delete');
    }

    #[On('click-delete-contact')]
    public function confirmDelete($id)
    {
        $this->contactId = $id;
        $this->isVisible = true;
        $this->dispatch('hide-listing');
      
    }


    public function delete(ContactService $contactService)
    {

        $contactService->deleteContact($this->contactId);

        session()->flash('message', 'Contact eliminato con successo.');
        $this->contactId = null;
        $this->isVisible = false;
        $this->dispatch('show-listing');
        $this->dispatch('content-refresh');
    }

    public function abort(){
        $this->isVisible = false;
        $this->dispatch('show-listing');
    }
}
