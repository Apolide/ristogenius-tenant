<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;
use App\Models\Tenantsubscription;
use App\Services\Tenant\TenantService;
use Illuminate\Support\Facades\Log;


class TenantController extends Controller
{

    public $tenantService;

    public function __construct()
    {
        $this->tenantService = new TenantService;
    }


    // public function chooseSubscription($tenant_id){

    //     $tenant = Tenant::findOrFail($tenant_id);

    //     if ($this->tenantService->hasActiveAccountSubscription($tenant))
    //         return view('stripe.subscription-already-active', ['tenant' => $tenant]);
  
    //     return view('stripe.choose-subscription', ['tenant' => $tenant]);

    // }



    public function accountSubscriptionThanks($tenant_id){

        $tenant = Tenant::findOrFail($tenant_id);

        return view('stripe.account-subscription-thanks', ['tenant' => $tenant]);

    }

    public function accountSubscriptionIncomplete($tenant_id){

        $tenant = Tenant::findOrFail($tenant_id);

        return view('stripe.account-subscription-incomplete', ['tenant' => $tenant]);

    }

    // INIZIO STRIPE CONNECT
/**
     * Avvia l'onboarding Stripe Connect per il tenant.
     * Crea l'account Connect se non esiste e reindirizza al flusso di onboarding.
     */
    public function startConnectOnboarding($tenant_id)
    {
        $tenant = Tenant::findOrFail($tenant_id);

        try {
            $onboardingUrl = $this->tenantService->createConnectOnboardingLink($tenant);
            return redirect($onboardingUrl);
        } catch (\Throwable $e) {
            Log::error('Errore avvio onboarding Stripe Connect', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Errore durante l’avvio dell’onboarding Stripe Connect. Riprova più tardi.',
            ]);
        }
    }

    /**
     * Se l'utente abbandona l'onboarding, Stripe lo rimanda alla refresh_url
     * e noi gli rigeneriamo un nuovo link di onboarding.
     */
    public function refreshConnectOnboarding($tenant_id)
    {
        $tenant = Tenant::findOrFail($tenant_id);

        try {
            $onboardingUrl = $this->tenantService->createConnectOnboardingLink($tenant);
            return redirect($onboardingUrl);
        } catch (\Throwable $e) {
            Log::error('Errore refresh onboarding Stripe Connect', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Errore nel ripristinare l’onboarding Stripe Connect. Riprova più tardi.',
            ]);
        }
    }

    /**
     * Stripe rimanda qui l'utente quando ha finito (o comunque è uscito) dall'onboarding.
     * Lo stato "vero" dell'account verrà aggiornato dal webhook account.updated.
     */
    public function returnConnectOnboarding($tenant_id)
    {
        $tenant = Tenant::findOrFail($tenant_id);

        return view('tenant.connect-onboarding-return', [
            'tenant' => $tenant,
        ]);
    }

    // FINE STRIPE CONNECT
}
