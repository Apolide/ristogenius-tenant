<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\Telegram\TelegramController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\WhatsappOnboardingController;

// Livewire
use App\Livewire\Products\ProductIndex;
use App\Livewire\Tenants\TenantIndex;
use App\Livewire\Suppliers\SupplierIndex;
use App\Livewire\Customers\CustomerIndex;

use App\Livewire\Funnels\FunnelIndex;
use App\Livewire\Funnels\ManageFunnelEmails;
use App\Livewire\Emails\EmailIndex;
use App\Livewire\Funnels\FunnelEmailTracking;

use App\Livewire\Whatsapp\ConversationBoard;
use App\Services\Customer\CustomerService;








/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::domain(env('DOMAIN'))
    ->middleware('guest:customer')
    ->get('/customer/_login_test', fn() => response('OK LOGIN TEST', 200));

Route::domain(env('DOMAIN'))->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Route::get('/customer/login', [CustomerController::class, 'customerLogin'])->name('customer.login');
    // Route::post('/customer/login', [CustomerController::class, 'customerLogin'])->middleware(['throttle:customer-login-email', 'throttle:customer-login-ip'])->name('customer.login.submit');
    // Route::get('/customer/{base64_customer_id}/profile', [CustomerController::class, 'profile']);
    // Route::get('/customer/{base64_customer_id}/fidelity', [CustomerController::class, 'fidelity']);
    // Route::get('/customer/fidelity/{base64_customer_id}/account/{account_id}', [CustomerController::class, 'fidelityAccount']);


    Route::get('/', fn () => view('welcome'));

    // Login customer: form + invio email
    Route::middleware('guest:customer')->group(function () {
        Route::get('/customer/login', [CustomerController::class, 'customerLogin'])
            ->name('customer.login');

        Route::post('/customer/login', [CustomerController::class, 'customerLogin'])
            ->middleware(['throttle:customer-login-email', 'throttle:customer-login-ip'])
            ->name('customer.login.submit');

        // Magic link (firmato + one-time token)
        Route::get('/customer/magic/{token}', [CustomerController::class, 'magicLogin'])
            ->middleware('signed')
            ->name('customer.magic');
    });

    // Area customer protetta
    Route::prefix('customer')
        ->middleware(['auth:customer', 'customer.last_action'])
        ->group(function () {

        Route::get('/profile', [CustomerController::class, 'profile'])->name('customer.profile');
        Route::get('/fidelity', [CustomerController::class, 'fidelity'])->name('customer.fidelity');
        Route::get('/fidelity/account/{account_id}', [CustomerController::class, 'fidelityAccount'])
            ->name('customer.fidelity.account');
        
        Route::get('/coupons', [CustomerController::class, 'coupons'])->name('customer.coupons');
        Route::get('/coupons/{customerCoupon}', [CustomerController::class, 'couponShow'])->name('customer.coupons.show');
        
        Route::get('/settings', [CustomerController::class, 'settings'])->name('customer.settings');
        Route::post('/settings/update/consent-marketing', [CustomerController::class, 'updateConsentMarketing'])->name('customer.settings.update.consent-marketing');
        Route::get('/delete', [CustomerController::class, 'delete'])->name('customer.delete');
        Route::delete('/profile', [CustomerController::class, 'destroy'])->name('customer.destroy');


        Route::post('/logout', [CustomerController::class, 'logout'])->name('customer.logout');
    });

    // Route::get('/dashboard', function () {
    //     // return view('dashboard');
    //     return redirect('/manage/tenants');
    // })->name('dashboard');

    // // Route::middleware([
    // //     'auth:sanctum',
    // //     config('jetstream.auth_session'),
    // //     'verified',
    // // ])->group(function () {
    // //     Route::get('/dashboard', function () {
    // //         // return view('dashboard');
    // //         return redirect('/manage/tenants');
    // //     })->name('dashboard');
    // // });

    // #Tenants
    // Route::get('/manage/tenants', TenantIndex::class);


    Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function () {

        
        #Tenants
        Route::get('/manage/tenants', TenantIndex::class);
        // Route::get('/manage/customers', CustomerIndex::class);


        // Dashboard con informazioni sui fondi
        // Route::get('/dashboard/{tenant?}', function ($tenant = null) {
        //     if ($tenant) {
        //         $tenantModel = App\Models\Tenant::findOrFail($tenant);
        //         $fundsService = app(App\Services\Funds\TenantFundsService::class);
        //         $funds = $fundsService->getTenantFunds($tenant);
        //         $stats = $fundsService->getFundStats($tenant);
        //         echo "<pre>"; print_r($funds); echo "</pre>";
        //         echo "<pre>"; print_r($stats); echo "</pre>";
        //         die;
                
        //         return view('dashboard', compact('tenantModel', 'funds', 'stats'));
        //     }
            
        //     return view('dashboard');
        // })->name('dashboard');
    
        // Pagina dedicata alla gestione fondi (protetta da auth)
        // Route::get('/my-funds/{tenant}', [App\Http\Controllers\FundsController::class, 'index'])
        //     ->name('my-funds');


        // Route::get('/manage/suppliers', SupplierIndex::class);
        
        #Customers
        //Route::get('/manage/customers', [App\Http\Controllers\CustomerController::class, 'customerslist']);


        // Route::get('/funnels/email/edit/{id}', [App\Http\Controllers\FunnelController::class, 'editEmail']);
        // Route::get('/funnels/{funnel_id}/emails', \App\Livewire\Funnels\ManageFunnelEmails::class)->name('funnel.emails.manage');

        // // Rotta principale per la gestione dei funnel (CRUD all’interno)
        // Route::get('/funnels', FunnelIndex::class)
        //     ->name('funnels.index');

        // // Rotta per gestire l’associazione delle email a un determinato funnel
        // // (visualizza la pagina con la tabella delle email associate,
        // //  e un form per aggiungere nuove email al funnel, ecc.)
        // // Route::get('/funnels/{funnel}/emails', ManageFunnelEmails::class)
        // //     ->name('funnel.emails.manage');

        // // Rotta principale per la gestione globale delle email (CRUD all’interno)
        // Route::get('/emails', EmailIndex::class)
        //     ->name('emails.index');
        
        // // Rotta principale per la gestione dei newsletter subscribers (CRUD Livewire)
        // Route::get('/manage/subscribers', \App\Livewire\NewsletterSubscribers\NewsletterSubscriberCrud::class)
        //     ->name('subscribers.index');

        // Route::get('/funnels/{funnel_id}/tracking', FunnelEmailTracking::class)
        //     ->name('funnels.tracking');

        // Route::get('/whatsapp/conversations', ConversationBoard::class)
        //     ->name('whatsapp.conversations');


     });
}); 



Route::domain(env('SUBDOMAIN_ADMIN'))->group(function () {

    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString()
        ]);
    });

    // Route::middleware([
    //     'auth:sanctum',
    //     config('jetstream.auth_session'),
    //     'verified',
    // ])->group(function () {
    //     Route::get('/dashboard', function () {
    //         // return view('dashboard');
    //         return redirect('/manage/tenants');
    //     })->name('dashboard');
    // });

    // https://admin.ristopilot.com/location/regions
    // https://admin.ristopilot.com/location/provinces/13
    // https://admin.ristopilot.com/location/comuni/13

    Route::get('/location/regions', [LocationController::class, 'getRegions'])->name('location.regions');
    Route::get('/location/provinces/{region}', [LocationController::class, 'getProvincesByRegion'])->name('location.provinces');
    Route::get('/location/comuni/{province}', [LocationController::class, 'getComuniByProvince'])->name('location.comuni');

    Route::group(['middleware' => ['auth:sanctum']], function () {
        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
        // Operations
        Route::get('/operations', [OperationController::class, 'index'])->name('operations.index');
    });

    // Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function () {

    //     #Tenants
    //     // Route::get('/manage/tenants', TenantIndex::class);
    //     Route::get('/manage/customers', CustomerIndex::class);

    //  });

});