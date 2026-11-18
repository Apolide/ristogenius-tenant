<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\UserController;
use App\Livewire\Bookings\BookingCreate;
// Livewire
use App\Livewire\Bookings\BookingEdit;
use App\Livewire\Bookings\BookingIndex;
use App\Livewire\Bookings\BookingRoomIndex;
use App\Livewire\Bookings\BookingShow;
use App\Livewire\Bookings\BookingWaitlistIndex;
use App\Livewire\Bookings\BookingWalkIn;
use App\Livewire\Contacts\ContactIndex;
use App\Livewire\Customers\CustomerEdit;
use App\Livewire\Customers\CustomerIndex;
use App\Livewire\Customers\CustomerShow;
use App\Livewire\Marketing\Forms\FormCreate as MarketingFormCreate;
use App\Livewire\Marketing\Forms\FormEdit as MarketingFormEdit;
use App\Livewire\Marketing\Forms\FormIndex as MarketingFormIndex;
use App\Livewire\Marketing\Forms\FormStyleEdit as MarketingFormStyleEdit;
use App\Livewire\Marketing\Forms\PublicForm as MarketingPublicForm;
use App\Livewire\Marketing\Forms\SubmissionIndex as MarketingSubmissionIndex;
use App\Livewire\Menu\IngredientIndex;
use App\Livewire\Menu\MenuIndex;
use App\Livewire\Menu\ProductIndex;
use App\Livewire\Menu\PublicMenu;
use App\Livewire\Personnel\PersonnelCreate;
use App\Livewire\Personnel\PersonnelEdit;
use App\Livewire\Personnel\PersonnelIndex;
use App\Livewire\Personnel\PersonnelPermissions;
use App\Livewire\Profile\ProfileEdit;
use App\Livewire\PublicBookings\PublicBookingEdit;
use App\Livewire\PublicBookings\PublicBookingShow;
use App\Livewire\Settings\BookingRemindHoursEdit;
use App\Livewire\Settings\Departments\DepartmentIndex;
use App\Livewire\Settings\MaxSittingTimeEdit;
use App\Livewire\Settings\MessageChannelCasesIndex;
use App\Livewire\Settings\MessageTemplatesIndex;
use App\Livewire\Settings\NotificationModesEdit;
use App\Livewire\Settings\OpeningHoursEdit;
use App\Livewire\Settings\PaxCapacityEdit;
use App\Livewire\Settings\Rooms\RoomIndex;
use App\Livewire\Settings\Rooms\RoomTablePlanner;
use App\Livewire\Settings\RoomTables\RoomTableIndex;
use App\Livewire\Settings\SettingsIndex;
use App\Livewire\Users\Userlist;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;

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

Route::domain(env('DOMAIN'))->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/', fn () => view('welcome'));

    Route::get('/privacy-policy', function (Request $request) {
        $language = strtolower((string) $request->query('language', app()->getLocale()));
        if (! array_key_exists($language, config('locales')) || ! view()->exists("legal.privacy-policy-{$language}")) {
            $language = 'it';
        }
        app()->setLocale($language);

        return view('layouts.legal', [
            'title' => __('marketing_forms.privacy_policy'),
            'slot' => new HtmlString(view("legal.privacy-policy-{$language}")->render()),
        ]);
    })->name('privacy-policy');

    Route::get('/poll/{invitation}', [\App\Http\Controllers\PublicPollController::class, 'show'])
        ->middleware(['signed:relative', 'throttle:60,1'])->name('polls.public');
    Route::post('/poll/{invitation}', [\App\Http\Controllers\PublicPollController::class, 'submit'])
        ->middleware(['signed:relative', 'throttle:15,1'])->name('polls.submit');

    Route::prefix('/fidelity')->name('fidelity.')->controller(\App\Http\Controllers\PublicFidelityController::class)->group(function () {
        Route::get('/subscribe', 'show')->name('subscribe');
        Route::post('/subscribe', 'subscribe')->middleware('throttle:5,1')->name('request');
        Route::get('/confirm', 'confirm')->middleware(['signed', 'throttle:30,1'])->name('confirm');
        Route::post('/confirm', 'activate')->middleware(['signed', 'throttle:10,1'])->name('activate');
        Route::get('/card/{token}', 'status')->whereAlphaNumeric('token')->middleware('throttle:60,1')->name('status');
        Route::get('/card/{token}/pdf', 'pdf')->whereAlphaNumeric('token')->middleware('throttle:10,1')->name('pdf');
    });

    Route::get('/giftcard/{language?}', [\App\Http\Controllers\PublicGiftCardController::class, 'show'])
        ->name('gift-cards.public');
    Route::post('/giftcard/{language?}', [\App\Http\Controllers\PublicGiftCardController::class, 'store'])
        ->middleware('throttle:5,1')->name('gift-cards.store');

    Route::get('/form/{language}/{form:slug}', MarketingPublicForm::class)->name('marketing.forms.public');
    Route::get('/coupons/{language}/{coupon:slug}', \App\Livewire\Marketing\Coupons\PublicCoupon::class)->middleware('throttle:60,1')->name('coupons.public');
    Route::get('/coupon-verify/{language}/{token}', [\App\Http\Controllers\MarketingPublicController::class, 'verify'])->middleware('throttle:30,1')->name('coupons.verify');
    Route::post('/coupon-verify/{language}/{token}', [\App\Http\Controllers\MarketingPublicController::class, 'confirm'])->middleware('throttle:10,1')->name('coupons.confirm');
    Route::get('/coupon/{language}/{token}/pdf', [\App\Http\Controllers\MarketingPublicController::class, 'pdf'])->middleware('throttle:60,1')->name('coupons.pdf');
    Route::get('/coupon/{language}/{token}/image', [\App\Http\Controllers\MarketingPublicController::class, 'image'])->middleware('throttle:60,1')->name('coupons.image');
    Route::get('/coupon/{language}/{token}', [\App\Http\Controllers\MarketingPublicController::class, 'card'])->middleware('throttle:60,1')->name('coupons.card');
    Route::get('/campaign-click/{delivery}', [\App\Http\Controllers\MarketingPublicController::class, 'click'])->middleware(['signed', 'throttle:60,1'])->name('campaigns.click');
    Route::match(['get', 'post'], '/marketing/unsubscribe/{language}/{token}', [\App\Http\Controllers\MarketingPublicController::class, 'unsubscribe'])->middleware('throttle:30,1')->name('marketing.unsubscribe');
    Route::get('/menu/{language}/{menu:slug}', PublicMenu::class)->name('menu.public');
    Route::get('/order/{language}/{menu:slug}', \App\Livewire\PublicOrders\Menu::class)->name('public-orders.menu');
    Route::get('/customer-order/{language}/{tableToken}', [\App\Http\Controllers\CustomerOrderAccessController::class, 'table'])->name('public-orders.table');
    Route::get('/fiscal-copy/{copy}/{document}', \App\Http\Controllers\FiscalCopyController::class)->middleware(['signed', 'throttle:20,1'])->name('fiscal-copy.download');
    Route::get('/order-access/{token}', [\App\Http\Controllers\CustomerOrderAccessController::class, 'show'])->middleware('throttle:30,1')->name('public-orders.verify');
    Route::post('/order-access/{token}', [\App\Http\Controllers\CustomerOrderAccessController::class, 'redeem'])->middleware('throttle:10,1')->name('public-orders.redeem');
    Route::get('/order-proposals/{token}', [\App\Http\Controllers\CustomerOrderProposalController::class, 'show'])->middleware('throttle:30,1')->name('public-orders.proposal');
    Route::post('/order-proposals/{token}', [\App\Http\Controllers\CustomerOrderProposalController::class, 'respond'])->middleware('throttle:10,1')->name('public-orders.proposal.respond');

    /*
    |--------------------------------------------------------------------------
    | Signed public customer booking routes
    |--------------------------------------------------------------------------
    |
    | These routes are intentionally isolated from authenticated management
    | routes. Customer/booking UUIDs must match and the URL signature prevents
    | parameters (including the customer-facing language) from being altered.
    |
    */
    Route::prefix('/customer/bookings')
        ->middleware(['signed:relative', \App\Http\Middleware\SetCustomerBookingLocale::class])
        ->name('public.bookings.')
        ->group(function (): void {
            Route::get('/{customer}/{booking}/{language}', PublicBookingShow::class)->name('show');
            Route::get('/{customer}/{booking}/{language}/edit', PublicBookingEdit::class)->name('edit');
        });

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/manage', BookingIndex::class)
            ->middleware('personnel.permission:'.PersonnelPermissionsService::BOOKINGS)
            ->name('manage');

        Route::middleware('personnel.permission:'.PersonnelPermissionsService::MARKETING)->group(function () {
            Route::prefix('/manage/fidelity')->name('fidelity.manage.')->controller(\App\Http\Controllers\FidelityController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'update')->name('update');
                Route::post('/rewards', 'reward')->name('rewards.create');
                Route::put('/rewards/{reward}', 'reward')->name('rewards.update');
                Route::delete('/rewards/{reward}', 'deleteReward')->name('rewards.delete');
            });
            Route::prefix('/manage/giftcard')->name('gift-cards.')
                ->controller(\App\Http\Controllers\GiftCardController::class)->group(function (): void {
                    Route::get('/', 'index')->name('index');
                    Route::get('/show/{giftCard}', 'show')->whereUuid('giftCard')->name('show');
                    Route::post('/{giftCard}/pay', 'pay')->whereUuid('giftCard')->name('pay');
                    Route::post('/{giftCard}/redeem', 'redeem')->whereUuid('giftCard')->name('redeem');
                });
            Route::get('/manage/coupons', \App\Livewire\Marketing\Coupons\CouponIndex::class)->name('coupons.index');
            Route::get('/manage/coupons/create', \App\Livewire\Marketing\Coupons\CouponEdit::class)->name('coupons.create');
            Route::get('/manage/coupons/{coupon}/edit', \App\Livewire\Marketing\Coupons\CouponEdit::class)->name('coupons.edit');
            Route::get('/manage/marketing/campaigns', \App\Livewire\Marketing\Campaigns\CampaignIndex::class)->name('campaigns.index');
            Route::get('/manage/marketing/campaigns/create', \App\Livewire\Marketing\Campaigns\CampaignEdit::class)->name('campaigns.create');
            Route::get('/manage/marketing/campaigns/{campaign}/edit', \App\Livewire\Marketing\Campaigns\CampaignEdit::class)->name('campaigns.edit');
            Route::get('/manage/marketing/campaigns/{campaign}/send', \App\Livewire\Marketing\Campaigns\CampaignSend::class)->name('campaigns.send');
            Route::get('/manage/contacts', ContactIndex::class);
            Route::get('/manage/leads', [LeadController::class, 'leadslist'])->name('leads.list');
            Route::prefix('/manage/polls')->name('polls.')->group(function (): void {
                Route::get('/', \App\Livewire\Polls\PollIndex::class)->name('index');
                Route::get('/create', \App\Livewire\Polls\PollEditor::class)->name('create');
                Route::get('/{poll}/edit', \App\Livewire\Polls\PollEditor::class)->name('edit');
                Route::get('/{poll}/responses', \App\Livewire\Polls\PollResponses::class)->name('responses');
            });
            Route::prefix('/manage/marketing/forms')->name('marketing.forms.')->group(function (): void {
                Route::get('/', MarketingFormIndex::class)->name('index');
                Route::get('/create', MarketingFormCreate::class)->name('create');
                Route::get('/responses', MarketingSubmissionIndex::class)->name('submissions');
                Route::get('/{form}/edit', MarketingFormEdit::class)->name('edit');
                Route::get('/{form}/edit/style', MarketingFormStyleEdit::class)->name('style');
            });
        });

        Route::middleware('personnel.permission:'.PersonnelPermissionsService::CUSTOMERS)->group(function () {
            Route::get('/manage/customers', CustomerIndex::class)->name('customers.index');
            Route::get('/manage/customers/show/{id}', CustomerShow::class)->name('customers.show');
            Route::get('/manage/customers/edit/{id}', CustomerEdit::class)->name('customers.edit');
        });

        Route::middleware('personnel.permission:'.PersonnelPermissionsService::BOOKINGS)->group(function () {
            Route::get('/manage/bookings', BookingIndex::class)->name('bookings.index');
            Route::get('/manage/bookings/rooms', BookingRoomIndex::class)->name('bookings.rooms');
            Route::get('/manage/bookings/create', BookingCreate::class)->name('bookings.create');
            Route::get('/manage/bookings/walkin', BookingWalkIn::class)->name('bookings.walk-in');
            Route::get('/manage/bookings/waitlist', BookingWaitlistIndex::class)->name('bookings.waitlist');
            Route::get('/manage/bookings/show/{booking}/{date?}', BookingShow::class)->name('bookings.show');
            Route::get('/manage/bookings/edit/{booking}', BookingEdit::class)->name('bookings.edit');
        });

        Route::middleware('personnel.permission:'.PersonnelPermissionsService::ORDERS)->group(function () {
            Route::get('/manage/orders', \App\Livewire\Orders\RoomMap::class)->name('orders.index');
            Route::get('/manage/orders/{order}', \App\Livewire\Orders\Editor::class)->name('orders.show');
        });
        Route::middleware('personnel.permission:'.PersonnelPermissionsService::CUSTOMER_ORDERS)->group(function () {
            Route::get('/manage/customers-orders', \App\Livewire\CustomerOrders\Index::class)->name('customers-orders.index');
            Route::get('/manage/customers-orders/{order}/proposal', \App\Livewire\CustomerOrders\ProposalEditor::class)->name('customers-orders.proposal');
            Route::get('/manage/customers-orders/{order}', \App\Livewire\CustomerOrders\Show::class)->name('customers-orders.show');
            Route::get('/manage/customers-orders/{order}/print', \App\Http\Controllers\CustomerOrderPrintController::class)->name('customers-orders.print');
        });
        Route::middleware('personnel.permission:'.PersonnelPermissionsService::CASH_REGISTER)->group(function () {
            Route::get('/manage/cash-desk/customers-orders', \App\Livewire\CashDesk\CustomerOrders::class)->name('cash-desk.customer-orders');
            Route::get('/manage/cash-desk', \App\Livewire\CashDesk\RoomMap::class)->name('cash-desk.index');
            Route::get('/manage/cash-desk/reconciliation', \App\Livewire\CashDesk\Reconciliation::class)->name('cash-desk.reconciliation');
            Route::get('/manage/cash-desk/shifts', \App\Livewire\CashDesk\Shifts::class)->name('cash-desk.shifts');
            Route::get('/manage/cash-desk/history', \App\Livewire\CashDesk\History::class)->name('cash-desk.history');
            Route::get('/manage/cash-desk/orders/{order}', \App\Livewire\CashDesk\Show::class)->name('cash-desk.show');
            Route::get('/manage/cash-desk/orders/{order}/fiscal', \App\Livewire\CashDesk\FiscalDocuments::class)->name('cash-desk.fiscal');
            Route::get('/manage/cash-desk/fiscal-documents/{document}/xml', \App\Http\Controllers\FiscalDocumentXmlController::class)->name('cash-desk.fiscal-xml');
            Route::get('/manage/cash-desk/fiscal-documents/{document}/pdf', \App\Http\Controllers\FiscalDocumentPdfController::class)->name('cash-desk.fiscal-pdf');
            Route::get('/manage/cash-desk/orders/{order}/print', \App\Http\Controllers\OrderPrintController::class)->name('cash-desk.print');
        });
        Route::middleware('personnel.permission:'.PersonnelPermissionsService::KDS)->group(function () {
            Route::get('/manage/kds', \App\Livewire\Kds\Board::class)->name('kds.index');
            Route::get('/manage/kds/{department}', \App\Livewire\Kds\Board::class)->name('kds.show');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/manage/menu/products', ProductIndex::class)->name('menu.products');
            Route::get('/manage/menu/ingredients', IngredientIndex::class)->name('menu.ingredients');
            Route::get('/manage/menu/menus', MenuIndex::class)->name('menu.menus');
            Route::get('/manage/profile', ProfileEdit::class)->name('profile.edit');
            Route::get('/manage/settings/marketing', \App\Livewire\Marketing\Settings::class)->name('settings.marketing');
            Route::get('/manage/settings', SettingsIndex::class)->name('settings.index');
            Route::get('/manage/settings/notification-modes', NotificationModesEdit::class)->name('settings.notification-modes');
            Route::get('/manage/settings/booking-remind-hours', BookingRemindHoursEdit::class)->name('settings.booking-remind-hours');
            Route::get('/manage/settings/max-sitting-time', MaxSittingTimeEdit::class)->name('settings.max-sitting-time');
            Route::get('/manage/settings/opening-hours', OpeningHoursEdit::class)->name('settings.opening-hours');
            Route::get('/manage/settings/pax-capacity', PaxCapacityEdit::class)->name('settings.pax-capacity');
            Route::get('/manage/settings/message-channel-cases', MessageChannelCasesIndex::class)->name('settings.message-channel-cases');
            Route::get('/manage/settings/messages', MessageTemplatesIndex::class)->name('settings.messages');
            Route::get('/manage/settings/rooms', RoomIndex::class)->name('settings.rooms');
            Route::get('/manage/settings/fiscal', \App\Livewire\Settings\Fiscal\Settings::class)->name('settings.fiscal');
            Route::get('/manage/settings/customers-orders', \App\Livewire\Settings\CustomerOrders\Settings::class)->name('settings.customer-orders');
            Route::get('/manage/settings/rooms/orders-tables-qr-codes', \App\Livewire\Settings\Rooms\OrderTableQrCodes::class)->name('settings.rooms.order-qr-codes');
            Route::get('/manage/settings/rooms/orders-tables-qr-codes/{table}/download', \App\Http\Controllers\OrderTableQrController::class)->name('settings.rooms.order-qr-download');
            Route::redirect('/manage/settings/rooms/printqr', '/manage/settings/rooms/orders-tables-qr-codes');

            Route::get('/manage/settings/room-tables', RoomTableIndex::class)->name('settings.room-tables');
            Route::get('/manage/rooms/{room}', RoomTablePlanner::class)->name('rooms.planner');
            Route::get('/manage/settimgs/departments', DepartmentIndex::class)->name('settings.departments');

            Route::get('/manage/users', Userlist::class)->name('users.index');
            Route::get('/manage/users/create', [UserController::class, 'usercreate'])->name('users.create');
            Route::get('/manage/users/edit/{id}', [UserController::class, 'useredit'])->name('users.edit');

            Route::get('/manage/personnel', PersonnelIndex::class)->name('personnel.index');
            Route::get('/manage/personnel/create', PersonnelCreate::class)->name('personnel.create');
            Route::get('/manage/personnel/permissions', PersonnelPermissions::class)->name('personnel.permissions');
            Route::get('/manage/personnel/{user}/edit', PersonnelEdit::class)->name('personnel.edit');
        });
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');
    });

});
