<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\Common\CommonService;
use Telegram\Bot\Laravel\Facades\Telegram;


class Contacts extends Component
{

    public $lang, $email, $phone, $firstname, $lastname, $body;

    public function render()
    {
        return view('livewire.contacts');
    }
    public function mount()
    {
        $this->lang= "it";
        $this->firstname = '';
        $this->lastname = '';
        $this->email = '';
        $this->phone = '';
        $this->body = '';

     
    }

    public function submit()
    {

        $this->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required | email',
            'body' => 'required | min:50'
        ]);

        if (!$this->phone)
            $this->phone = 'Non specificato';

        Telegram::sendMessage([
            'chat_id' => config('telegram.signals_chan'),
            'text' => "☝🏽 New contact request\n\n Lang:\n".$this->lang."\n\n from: \n \n \n Name: \n" . $this->firstname . " \n \n Lastname: \n" . $this->lastname . " \n \n Email: \n" . $this->email . " \n \n Telefono: \n" . $this->phone . " \n \n Message: \n" . $this->body,
            'parse_mode' => 'html'
        ]);

        $this->reset();
        $message = "Grazie per il tuo messaggio, ti risponderemo il prima possibile.";
        session()->flash('success_contacts_message', $message);
    }

    // public function switchLanguage($locale){
        
    //     $this->lang = $locale;
    //     CommonService::setLocale($locale);
        
    // }
}
