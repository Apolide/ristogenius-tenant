<?php

namespace App\Services\Tenant;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Openinghour;
use App\Models\Tenant;
use App\Models\Tenantsubscription;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Jobs\External\Account\SetHasValidPaymentMethodExternalJob;


class TenantService
{
    public function getTenants(){
    
        return Tenant::all();

    }

    public function getTenantById($id){

        return Tenant::findOrFail($id);
    }

    public function getTenantByIdWithoutFail($id){

        return Tenant::find($id);
    }

    public function createTenant(array $data){
        
        $data["system_name"] = strtolower($data["system_name"]);
        $data["system_name"] = str_replace(" ", "", $data["system_name"]);
        $data["system_name"] = Str::slug($data["system_name"]);

        return Tenant::create($data);
    
    }

    public function updateTenant($id, array $data){
        
        $room = Tenant::findOrFail($id);

        return $room->update($data);
    
    }

    public function deleteTenant($id){

        return Tenant::destroy($id);

    }


    public function setupIntent($id){

        $tenant = Tenant::findOrFail($id);

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'setup',
            'success_url' => config('app.payments_url').'/stripe/setup/completed/'.$tenant->id,
            'cancel_url' => config('app.payments_url').'/stripe/setup/'.$tenant->id,
        ]);

        $tenant->stripe_session_id = $session['id'];
        if($tenant->save()){
            return $session['url'];
        }

    }


    public function getByStripeSessionId($stripe_session_id){
        
        return Tenant::where([['stripe_session_id','=',$stripe_session_id]])->first();
    
    }


    // from webhook
    public function setupIntentCreated($session){

        $tenant = $this->getByStripeSessionId($session->id);
       
        if (!$tenant){
            return;
        }
        
        $tenant->setup_intent = $session->setup_intent;

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        
        $setupIntent = $stripe->setupIntents->retrieve($tenant->setup_intent, []);
        
        if ($tenant->stripe_customer_id){
            $customer =  $stripe->customers->retrieve($tenant->stripe_customer_id , []);
            if ($tenant->payment_method){
                $stripe->paymentMethods->detach($tenant->payment_method, []);
            }
        }else{
            $customer = $stripe->customers->create([
                'name' => $tenant->name,
                'email' => $tenant->email
            ]);
        }
        
        $stripe->paymentMethods->attach(
            $setupIntent->payment_method,
            ['customer' => $customer->id]
        );

        $stripe->customers->update(
            $customer->id,
            ['invoice_settings' => ['default_payment_method' => $setupIntent->payment_method]]
        );

        $tenant->stripe_customer_id = $customer->id;
        $tenant->payment_method = $setupIntent->payment_method;
        
        $tenant->save();

        $payload['tenant_system_name'] = $tenant->system_name;
        SetHasValidPaymentMethodExternalJob::dispatch($payload);

      
    }

    public function shouldChooseAccountSubscription($tenant){

        $subscriptions = $tenant->subscriptions;
        $response = true;
     

        if ($subscriptions->count() == 0)
            return true;

        if ($this->hasActiveAccountSubscription($tenant))
            $response = false;

        if ($this->hasIncompleteAccountSubscription($tenant))
            $response = false;

        if ($this->hasPastDueAccountSubscription($tenant))
            $response = false;

        return $response;
    }

    // public function shouldRepairAccountSubscription($tenant){

    //     $subscriptions = $tenant->subscriptions;
    //     $response = false;

    //     if ($subscriptions->count() == 0)
    //         return false;

    //     if ($this->hasIncompleteAccountSubscription($tenant))
    //         return true;

    //     if ($this->hasPastDueAccountSubscription($tenant))
    //         return true;

    //     return $response;
    // }


    public function hasActiveAccountSubscription($tenant){

        $subscriptions = $tenant->subscriptions;

        if ($subscriptions->count() == 0)
            return false;

        foreach ($subscriptions as $key => $subscription) {

            if ($subscription->type == "account"){

                if ($subscription->status == "active"){

                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                    $stripeSubscription = $stripe->subscriptions->retrieve($subscription->subscription_id, []);
            
                    $isActive = ($stripeSubscription->status == "active") ? true : false;

                    if ($isActive)
                        return true;
                }
                    
            }
        }

        return false;
    }

    public function hasIncompleteAccountSubscription($tenant){

        $subscriptions = $tenant->subscriptions;

        if ($subscriptions->count() == 0)
            return false;

        foreach ($subscriptions as $key => $subscription) {

            if ($subscription->type == "account"){

                if ($subscription->status == "incomplete"){

                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                    $stripeSubscription = $stripe->subscriptions->retrieve($subscription->subscription_id, []);
            
                    $isIncomplete = ($stripeSubscription->status == "incomplete") ? true : false;

                    if ($isIncomplete)
                        return true;
                }
                    
            }
        }

        return false;
    }

    public function hasPastDueAccountSubscription($tenant){

        $subscriptions = $tenant->subscriptions;

        if ($subscriptions->count() == 0)
            return false;

        foreach ($subscriptions as $key => $subscription) {

            if ($subscription->type == "account"){

                if ($subscription->status == "past_due"){

                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                    $stripeSubscription = $stripe->subscriptions->retrieve($subscription->subscription_id, []);
            
                    $isPastDue = ($stripeSubscription->status == "past_due") ? true : false;

                    if ($isPastDue)
                        return true;
                }
                    
            }
        }

        return false;
    }

    public function selectAccountSubscription($tenant_id, $price_id){
        
        $tenant = Tenant::findOrFail($tenant_id);
        
        if (!$tenant->stripe_customer_id){
            return redirect("/stripe/setup/".$tenant->id);
        }

        if (!$this->shouldChooseAccountSubscription($tenant))
            return view('tenant.subscription-already-active', ['tenant' => $tenant]);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        
        try {
            $subscription = $stripe->subscriptions->create([
                'customer' => $tenant->stripe_customer_id,
                'items' => [['price' => $price_id]],
                'default_payment_method' => $tenant->payment_method,
                'off_session' => true,
                'collection_method' => 'charge_automatically',
                'payment_behavior' => 'error_if_incomplete',
                
                //'payment_behavior' => 'allow_incomplete'
                
            ]);

            $tenantSubscription = new Tenantsubscription;
            $tenantSubscription->tenant_id = $tenant->id;
            $tenantSubscription->type = "account";
            $tenantSubscription->subscription_id = $subscription->id;
            $tenantSubscription->status = $subscription->status;
            $tenantSubscription->save();

            return redirect(config('app.payments_url').'/tenant/account-subscription-thanks/'.$tenant->id);

        } catch (\Throwable $th) {
            return redirect(config('app.payments_url').'/tenant/account-subscription-incomplete/'.$tenant->id);
        }
        
    }


    public function getBySubscription($subscription_id){

        return Tenantsubscription::where('subscription_id', $subscription_id)->first();

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

    // INIZIO STRIPE CONNECT

    /**
     * Restituisce il tenant a partire dallo stripe_connect_account_id
     */
    public function getByStripeConnectAccountId(string $accountId): ?Tenant
    {
        return Tenant::where('stripe_connect_account_id', $accountId)->first();
    }

    /**
     * Crea un account Stripe Connect (Express) per il tenant se non esiste.
     * Restituisce il tenant aggiornato.
     */
    public function ensureConnectAccount(Tenant $tenant): Tenant
    {
        if ($tenant->stripe_connect_account_id) {
            return $tenant;
        }

        if ($tenant->id != "9eb63394-2182-45c3-a095-b66958f2adcd"){
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        }else{
            $stripe = new \Stripe\StripeClient(env('STRIPE_TEST_SECRET'));
        }

        $account = $stripe->accounts->create([
            'type' => 'express',
            'country' => 'IT', // se lavori solo in Italia, va bene fisso IT
            'email' => $tenant->email,
            'business_profile' => [
                'name' => $tenant->company_name ?: $tenant->name,
                // URL pubblica del tenant o del tuo sito
                'url' => config('app.url'), 
            ],
            'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers'      => ['requested' => true],
            ],
        ]);

        $tenant->stripe_connect_account_id = $account->id;
        $tenant->save();

        return $tenant;
    }

    /**
     * Crea un link di onboarding Stripe Connect per il tenant.
     * Se l'account non esiste, lo crea.
     */
    public function createConnectOnboardingLink(Tenant $tenant): string
    {
        $tenant = $this->ensureConnectAccount($tenant);

        if ($tenant->id != "9eb63394-2182-45c3-a095-b66958f2adcd"){
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        }else{
            $stripe = new \Stripe\StripeClient(env('STRIPE_TEST_SECRET'));
        }

        $accountLink = $stripe->accountLinks->create([
            'account' => $tenant->stripe_connect_account_id,
            'refresh_url' => config('app.payments_url') . '/tenant/' . $tenant->id . '/connect/onboarding/refresh',
            'return_url'  => config('app.payments_url') . '/tenant/' . $tenant->id . '/connect/onboarding/return',
            'type'        => 'account_onboarding',
        ]);

        return $accountLink->url;
    }

    /**
     * (già ti avevo proposto) sincronizza account Connect da webhook account.updated
     */
    public function syncConnectAccountFromStripe(\Stripe\Account $account): void
    {
        $tenant = $this->getByStripeConnectAccountId($account->id);

        if (!$tenant) {
            Log::warning('Stripe Connect account.updated senza tenant associato', [
                'stripe_account_id' => $account->id,
            ]);
            return;
        }

        $tenant->stripe_connect_charges_enabled = (bool) $account->charges_enabled;
        $tenant->stripe_connect_payouts_enabled  = (bool) $account->payouts_enabled;

        // triggerare alert in caso charges_enabled va a false 

        if ($account->details_submitted && !$tenant->stripe_connect_onboarding_completed_at) {
            $tenant->stripe_connect_onboarding_completed_at = now();
        }

        $tenant->save();
    }

    // FINE STRIPE CONNECT

}