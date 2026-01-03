<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Contact;
use App\Services\Contact\ContactService;
use Illuminate\Support\Facades\Log;


class ContactController extends Controller
{

    public $contactService;

    public function __construct()
    {
        $this->contactService = new ContactService;
    }


    // TODO REMOVE
    public function accountSubscriptionThanks($contact_id){

        $contact = Contact::findOrFail($contact_id);

        return view('stripe.account-subscription-thanks', ['contact' => $contact]);

    }

    // TODO REMOVE
    public function accountSubscriptionIncomplete($contact_id){

        $contact = Contact::findOrFail($contact_id);

        return view('stripe.account-subscription-incomplete', ['contact' => $contact]);

    }

    // TODO REMOVE
    /**
     * Avvia l'onboarding Stripe Connect per il tenant.
     * Crea l'account Connect se non esiste e reindirizza al flusso di onboarding.
     */
    public function startConnectOnboarding($contact_id)
    {
        $contact = Contact::findOrFail($contact_id);

        try {
            $onboardingUrl = $this->contactService->createConnectOnboardingLink($contact);
            return redirect($onboardingUrl);
        } catch (\Throwable $e) {
            Log::error('Errore avvio onboarding Stripe Connect', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Errore durante l’avvio dell’onboarding Stripe Connect. Riprova più tardi.',
            ]);
        }
    }

    /** TODO REMOVE
     * Se l'utente abbandona l'onboarding, Stripe lo rimanda alla refresh_url
     * e noi gli rigeneriamo un nuovo link di onboarding.
     */
    public function refreshConnectOnboarding($contact_id)
    {
        $contact = Contact::findOrFail($contact_id);

        try {
            $onboardingUrl = $this->contactService->createConnectOnboardingLink($contact);
            return redirect($onboardingUrl);
        } catch (\Throwable $e) {
            Log::error('Errore refresh onboarding Stripe Connect', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Errore nel ripristinare l’onboarding Stripe Connect. Riprova più tardi.',
            ]);
        }
    }

    /** TODO REMOVE
     * Stripe rimanda qui l'utente quando ha finito (o comunque è uscito) dall'onboarding.
     * Lo stato "vero" dell'account verrà aggiornato dal webhook account.updated.
     */
    public function returnConnectOnboarding($contact_id)
    {
        $contact = Contact::findOrFail($contact_id);

        return view('tenant.connect-onboarding-return', [
            'contact' => $contact,
        ]);
    }

    // FINE STRIPE CONNECT
}
