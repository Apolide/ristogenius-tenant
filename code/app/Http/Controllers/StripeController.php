<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use App\Models\Tenantsubscription;
use App\Services\Stripe\StripeService;
use App\Services\Tenant\TenantService;
use App\Services\Supplier\SupplierService;
use App\Services\Funds\FundsPaymentService;
use App\Jobs\Payments\InvoiceSubscriptionHandlerJob;
use App\Jobs\External\Account\EnableAccountExternalJob;
use App\Jobs\Payments\SetTenantsMonthlyPaidJob;
use App\Jobs\Payments\SetTenantsMonthlyFailedJob;

class StripeController extends Controller
{
    public $stripeService;
    public $tenantService;
    public $supplierService;
    public $fundsPaymentService;

    public function __construct()
    {
        $this->stripeService = new StripeService;
        $this->tenantService = new TenantService;
        $this->supplierService = new SupplierService;
        $this->fundsPaymentService = app(FundsPaymentService::class);
    }

    public function webhook(){

        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $endpoint_secret = env('STRIPE_ENDPOINT_SECRET');
        
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            http_response_code(401);
            exit();
        }
        
        // Handle the event
        switch ($event->type) {

        case 'account.updated':
            $account = $event->data->object; 
        break;

        case 'setup_intent_succeded':
        break;

        case 'checkout.session.expired':
            $session = $event->data->object;
            Log::debug('checkout.session.expired');            
        break;

        case 'checkout.session.completed':
            $session = $event->data->object;

            if ($session->mode == 'setup' && $session->status == 'complete'){
                $this->stripeService->setupIntentCreated($session);
            }

            if ($session->mode == 'payment' && $session->status == 'complete' && $session->payment_status == 'paid'){
                $this->fundsPaymentService->handleSuccessfulFundsPurchase(
                    $session->payment_intent,
                    $session->metadata->model_id
                );  
            }

        break;

        case 'invoice.payment_succeeded':
            $object = $event->data->object;

            if ($object->subscription){
                InvoiceSubscriptionHandlerJob::dispatch($object->subscription, "payment_succeeded");
            }
        break;

        case 'invoice.payment_failed':
            $object = $event->data->object;

            if ($object->subscription){
                InvoiceSubscriptionHandlerJob::dispatch($object->subscription, "payment_failed");
            }
        break;

        case 'invoice.payment_action_required':
            $object = $event->data->object;

            if ($object->subscription){
                InvoiceSubscriptionHandlerJob::dispatch($object->subscription, "payment_action_required");
            }
        break;

        case 'radar.early_fraud_warning.created':
            $radar = $event->data->object;
            Log::debug("radar.early_fraud_warning");
        break;

        case 'invoice.paid':
        break;

        case 'invoice.sent':
            $object = $event->data->object;
        break;

        case 'payment_intent.succeeded':
            $object = $event->data->object;

            // Gestione pagamento acquisto fondi
            if ($object->metadata->entity == "tenant" && $object->metadata->charge_reason == "funds_purchase"){
                $this->fundsPaymentService->handleSuccessfulFundsPurchase(
                    $object->id,
                    $object->metadata->model_id
                );
            }
            
            // Gestione pagamenti mensili esistenti (manteniamo retrocompatibilità)
            if ($object->metadata->entity == "tenant" && $object->metadata->charge_reason == "platform_messaging"){
                SetTenantsMonthlyPaidJob::dispatch($object->metadata->model_id);
            }
        break;

        case 'payment_intent.payment_failed':
            $object = $event->data->object;

            // Gestione fallimento acquisto fondi
            if ($object->metadata->entity == "tenant" && $object->metadata->charge_reason == "funds_purchase"){
                $this->fundsPaymentService->handleFailedFundsPurchase($object->metadata->model_id);
            }
            
            // Gestione fallimenti pagamenti mensili esistenti
            if ($object->metadata->entity == "tenant" && $object->metadata->charge_reason == "platform_messaging"){
                SetTenantsMonthlyFailedJob::dispatch($object->metadata->model_id);
            }
        break;

        default:
            error_log('Received unknown event type');
        }
        
        http_response_code(200);
    }

    // Metodi esistenti mantenuti...
    public function setupIntent($id){
        return redirect($this->stripeService->setupIntent($id));
    }

    public function setupCompleted($id){
        if ($this->tenantService->getTenantByIdWithoutFail($id)){
            $tenant = $this->tenantService->getTenantByIdWithoutFail($id);
            return $this->setupNext($tenant->id);
        }

        if ($this->supplierService->getSupplierByIdWithoutFail($id)){
            $supplier = $this->supplierService->getSupplierByIdWithoutFail($id);
            return $this->setupNext($supplier->id);
        }
    }

    public function setupNext($id){
        if ($this->tenantService->getTenantByIdWithoutFail($id)){
            $tenant = $this->tenantService->getTenantByIdWithoutFail($id);

            if ($this->tenantService->shouldChooseAccountSubscription($tenant)){
                return view('tenant.choose-account-subscription', ['tenant' => $tenant]);
            }

            return view('stripe.setup-completed');
        }

        if ($this->supplierService->getSupplierByIdWithoutFail($id)){
            $supplier = $this->supplierService->getSupplierByIdWithoutFail($id);
            return view('stripe.setup-completed');
        }
    }

    public function selectAccountSubscription($id, $price_id){
        if ($this->tenantService->getTenantByIdWithoutFail($id)){
            return $this->tenantService->selectAccountSubscription($id, $price_id);
        }
    }

    // Nuovi metodi per la gestione dei fondi
    public function createFundsPurchaseSession($tenantId, $amountEuros){
        $successUrl = config('app.payments_url').'/funds/purchase/success';
        $cancelUrl = config('app.payments_url').'/funds/purchase/cancel';
        
        try {
            $result = $this->fundsPaymentService->createFundsPurchaseSession(
                $tenantId, 
                $amountEuros, 
                $successUrl, 
                $cancelUrl
            );
            
            return redirect($result['session_url']);
        } catch (\Exception $e) {
            Log::error("Error creating funds purchase session: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Errore nella creazione della sessione di pagamento']);
        }
    }

    public function directFundsPurchase($tenantId, $amountEuros){
        try {
            $result = $this->fundsPaymentService->createDirectFundsPurchase($tenantId, $amountEuros);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fondi acquistati con successo',
                    'purchase_id' => $result['purchase_id']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore nell\'acquisto dei fondi: ' . $result['error'],
                    'purchase_id' => $result['purchase_id']
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error("Error in direct funds purchase: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Errore interno del server'
            ], 500);
        }
    }


    // INIZIO STRIPE CONNECT

    // === NUOVO: webhook per Stripe CONNECT ===
    public function connectWebhook()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $endpoint_secret = env('STRIPE_CONNECT_ENDPOINT_SECRET');

        
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe Connect webhook payload non valido', [
                'error' => $e->getMessage(),
            ]);
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe Connect webhook firma non valida', [
                'error' => $e->getMessage(),
            ]);
            http_response_code(401);
            exit();
        }

        // Con Connect hai sempre l'account collegato
        $connectedAccountId = $event->account ?? null;

        switch ($event->type) {

            case 'account.updated':
                $account = $event->data->object; // \Stripe\Account
                // sincronizzo lo stato dell'account sul tenant
                $this->tenantService->syncConnectAccountFromStripe($account);
            break;

            case 'checkout.session.completed':
                $session = $event->data->object;

                if ($session->mode === 'payment' && $session->payment_status === 'paid') {
                    $metadata = $session->metadata ?? (object)[];

                    if (($metadata->entity ?? null) === 'tenant_takeaway_order') {
                        $tenantId        = $metadata->tenant_id ?? null;
                        $takeawayOrderId = $metadata->takeaway_order_id ?? null;

                        if ($tenantId && $takeawayOrderId) {
                            $tenant = $this->tenantService->getTenantByIdWithoutFail($tenantId);

                            if ($tenant) {
                                // 👇 chiamata HTTP verso l'app del tenant
                                $this->notifyTenantAppOrderPaid($tenant, $takeawayOrderId, $session);
                            }
                        }
                    }
                }
            break;

            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                // Trovo il tenant a cui appartiene l'account Connect
                $tenant = $connectedAccountId
                    ? $this->tenantService->getByStripeConnectAccountId($connectedAccountId)
                    : null;

                if (!$tenant) {
                    Log::warning('Connect payment_intent.succeeded senza tenant associato', [
                        'account' => $connectedAccountId,
                        'payment_intent' => $paymentIntent->id,
                    ]);
                    break;
                }

                $metadata = $paymentIntent->metadata ?? null;

                /**
                 * QUI differenzi le logiche dei pagamenti per conto dei clienti.
                 * 
                 * Esempio di convenzione suggerita quando crei il PaymentIntent:
                 *  - metadata.entity = 'tenant'
                 *  - metadata.charge_reason = 'customer_payment'
                 *  - metadata.order_id (o quello che vuoi)
                 */

                if ($metadata && isset($metadata->charge_reason) && $metadata->charge_reason === 'customer_payment') {
                    // 👇 Qui dentro metti la logica "pagamento cliente finale per conto del tenant"
                    // per esempio:
                    // app(\App\Services\Connect\TenantCustomerPaymentService::class)
                    //    ->handleSuccess($tenant, $paymentIntent);
                }

            break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $metadata = $paymentIntent->metadata ?? (object)[];

                if (($metadata->entity ?? null) === 'tenant_takeaway_order') {
                    $tenantId        = $metadata->tenant_id ?? null;
                    $takeawayOrderId = $metadata->takeaway_order_id ?? null;

                    if ($tenantId && $takeawayOrderId) {
                        $tenant = $this->tenantService->getTenantByIdWithoutFail($tenantId);

                        if ($tenant) {
                            $this->notifyTenantAppOrderPaymentFailed($tenant, $takeawayOrderId, $paymentIntent);
                        }
                    }
                }
            break;

            case 'charge.refunded':
                $charge = $event->data->object;

                // Se vuoi leggere metadata dal charge/payment_intent
                // e notificare la tenant app che un refund è stato effettuato (es. manuale da dashboard Stripe)
                // puoi fare qualcosa di simile a notifyTenantAppOrderPaid()/failed
            break;

            default:
                Log::info('Stripe Connect webhook event non gestito', [
                    'type' => $event->type,
                    'account' => $connectedAccountId,
                ]);
        }

        http_response_code(200);
    }


   public function connectTestWebhook()
    {

        \Stripe\Stripe::setApiKey(env('STRIPE_TEST_SECRET'));
        $endpoint_secret = env('STRIPE_TEST_CONNECT_ENDPOINT_SECRET');
        
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? null;
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe Connect webhook payload non valido', [
                'error' => $e->getMessage(),
            ]);
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe Connect webhook firma non valida', [
                'error' => $e->getMessage(),
            ]);
            http_response_code(401);
            exit();
        }

        // Con Connect hai sempre l'account collegato
        $connectedAccountId = $event->account ?? null;

        switch ($event->type) {

            case 'account.updated':
                $account = $event->data->object; // \Stripe\Account
                // sincronizzo lo stato dell'account sul tenant
                $this->tenantService->syncConnectAccountFromStripe($account);
            break;

            case 'checkout.session.completed':
                $session = $event->data->object;

                if ($session->mode === 'payment' && $session->payment_status === 'paid') {
                    $metadata = $session->metadata ?? (object)[];

                    if (($metadata->entity ?? null) === 'tenant_takeaway_order') {
                        $tenantId        = $metadata->tenant_id ?? null;
                        $takeawayOrderId = $metadata->takeaway_order_id ?? null;

                        if ($tenantId && $takeawayOrderId) {
                            $tenant = $this->tenantService->getTenantByIdWithoutFail($tenantId);

                            if ($tenant) {
                                // 👇 chiamata HTTP verso l'app del tenant
                                $this->notifyTenantAppOrderPaid($tenant, $takeawayOrderId, $session);
                            }
                        }
                    }
                }
            break;

            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                // Trovo il tenant a cui appartiene l'account Connect
                $tenant = $connectedAccountId
                    ? $this->tenantService->getByStripeConnectAccountId($connectedAccountId)
                    : null;

                if (!$tenant) {
                    Log::warning('Connect payment_intent.succeeded senza tenant associato', [
                        'account' => $connectedAccountId,
                        'payment_intent' => $paymentIntent->id,
                    ]);
                    break;
                }

                $metadata = $paymentIntent->metadata ?? null;

                /**
                 * QUI differenzi le logiche dei pagamenti per conto dei clienti.
                 * 
                 * Esempio di convenzione suggerita quando crei il PaymentIntent:
                 *  - metadata.entity = 'tenant'
                 *  - metadata.charge_reason = 'customer_payment'
                 *  - metadata.order_id (o quello che vuoi)
                 */

                if ($metadata && isset($metadata->charge_reason) && $metadata->charge_reason === 'customer_payment') {
                    // 👇 Qui dentro metti la logica "pagamento cliente finale per conto del tenant"
                    // per esempio:
                    // app(\App\Services\Connect\TenantCustomerPaymentService::class)
                    //    ->handleSuccess($tenant, $paymentIntent);
                }

            break;

            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $metadata = $paymentIntent->metadata ?? (object)[];

                if (($metadata->entity ?? null) === 'tenant_takeaway_order') {
                    $tenantId        = $metadata->tenant_id ?? null;
                    $takeawayOrderId = $metadata->takeaway_order_id ?? null;

                    if ($tenantId && $takeawayOrderId) {
                        $tenant = $this->tenantService->getTenantByIdWithoutFail($tenantId);

                        if ($tenant) {
                            $this->notifyTenantAppOrderPaymentFailed($tenant, $takeawayOrderId, $paymentIntent);
                        }
                    }
                }
            break;

            case 'charge.refunded':
                $charge = $event->data->object;

                // Se vuoi leggere metadata dal charge/payment_intent
                // e notificare la tenant app che un refund è stato effettuato (es. manuale da dashboard Stripe)
                // puoi fare qualcosa di simile a notifyTenantAppOrderPaid()/failed
            break;

            default:
                Log::info('Stripe Connect webhook event non gestito', [
                    'type' => $event->type,
                    'account' => $connectedAccountId,
                ]);
        }

        http_response_code(200);
    }    

    /**
     * Esempio di chiamata all'app tenant per marcare ordine come pagato.
     */
    protected function notifyTenantAppOrderPaid(\App\Models\Tenant $tenant, string $takeawayOrderId, $session): void
    {
        // Qui devi decidere COME raggiungere l'app del tenant.
        // Potresti avere un mapping tipo "https://{system_name}.example.com"
        $tenantAppBaseUrl = "https://".$tenant->system_name.".ristopilot.com"; 
        // es: "https://%s.tenant.yourdomain.com"

        $url = sprintf($tenantAppBaseUrl, $tenant->system_name) . '/api/takeaway/orders/payment/success';

        try {
            \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SYSTEM_TOKEN'),
            ])->post($url, [
                'order_id'         => $takeawayOrderId,
                'stripe_session_id'=> $session->id,
                'stripe_payment_intent_id' => $session->payment_intent,
                
                
            ]);
        } catch (\Throwable $e) {
            Log::error('Errore chiamata app tenant per ordine pagato', [
                'tenant_id' => $tenant->id,
                'order_id'  => $takeawayOrderId,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    protected function notifyTenantAppOrderPaymentFailed(\App\Models\Tenant $tenant, string $takeawayOrderId, $paymentIntent): void
    {
        
        $tenantAppBaseUrl = "https://".$tenant->system_name.".ristopilot.com"; 
        $url = sprintf($tenantAppBaseUrl, $tenant->system_name) . '/api/takeaway/orders/payment/failed';

        try {
            \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . env('SYSTEM_TOKEN'),
            ])->post($url, [
                'order_id'         => $takeawayOrderId,
                'stripe_payment_intent_id' => $paymentIntent->id,
                
            ]);
        } catch (\Throwable $e) {
            Log::error('Errore chiamata app tenant per ordine payment_failed', [
                'tenant_id' => $tenant->id,
                'order_id'  => $takeawayOrderId,
                'error'     => $e->getMessage(),
            ]);
        }
    }


    

    // FINE STRIPE CONNECT



}