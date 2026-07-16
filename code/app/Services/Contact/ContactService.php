<?php

namespace App\Services\Contact;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Openinghour;
use App\Models\Contact;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Jobs\External\Account\SetHasValidPaymentMethodExternalJob;


class ContactService
{
    public function getContacts(){
    
        return Contact::all();

    }

    public function getContactById($id){

        return Contact::findOrFail($id);
    }

    public function getContactByIdWithoutFail($id){

        return Contact::find($id);
    }

    public function createContact(array $data){
        
        $data["system_name"] = strtolower($data["system_name"]);
        $data["system_name"] = str_replace(" ", "", $data["system_name"]);
        $data["system_name"] = Str::slug($data["system_name"]);

        return Contact::create($data);
    
    }

    public function updateContact($id, array $data){
        
        $room = Contact::findOrFail($id);

        return $room->update($data);
    
    }

    public function deleteContact($id){

        return Contact::destroy($id);

    }


    // TODO REMOVE
    public function setupIntent($id){

        $contact = Contact::findOrFail($id);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'setup',
            'success_url' => config('app.payments_url').'/stripe/setup/completed/'.$contact->id,
            'cancel_url' => config('app.payments_url').'/stripe/setup/'.$contact->id,
        ]);

        $contact->stripe_session_id = $session['id'];
        if($contact->save()){
            return $session['url'];
        }

    }

    // TODO REMOVE
    public function getByStripeSessionId($stripe_session_id){
        
        return Contact::where([['stripe_session_id','=',$stripe_session_id]])->first();
    
    }


    /**
     * Genera stringa casuale con caratteri permessi
     * 
     * @param int $length
     * @return string
     */
    public function generateRandomString(int $length = 35): string
    {
        $allowedChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $string = '';
        
        for ($i = 0; $i < $length; $i++) {
            $string .= $allowedChars[random_int(0, strlen($allowedChars) - 1)];
        }
        
        return $string;
    }


}