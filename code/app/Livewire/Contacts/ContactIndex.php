<?php

namespace App\Livewire\Contacts;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use App\Services\Contact\ContactService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ContactIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $isVisible = true;


    public function updatingSearch()
    {
        $this->resetPage();
    }


    public function render(ContactService $contactService)
    {
        $contacts = $contactService->getContacts();
       
        return view('livewire.contacts.contact-index', [
            'contacts' => $contacts,
        ]);
    }

    #[On('content-refresh')]
    public function refreshView(ContactService $contactService)
    {
    
        $contacts = $contactService->getContacts();
    

        return view('livewire.contacts.contact-index', [
            'contacts' => $contacts,
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
