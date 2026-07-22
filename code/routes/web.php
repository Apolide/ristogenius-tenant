<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\UserController;
use App\Livewire\Bookings\BookingCreate;
// Livewire
use App\Livewire\Bookings\BookingEdit;
use App\Livewire\Bookings\BookingIndex;
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
use App\Livewire\Menu\IngredientIndex;
use App\Livewire\Menu\MenuIndex;
use App\Livewire\Menu\ProductIndex;
use App\Livewire\Menu\PublicMenu;
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
        if (! in_array($language, ['it', 'en', 'de'], true)) {
            $language = 'it';
        }
        app()->setLocale($language);

        return view('layouts.legal', [
            'title' => __('marketing_forms.privacy_policy'),
            'slot' => new HtmlString(view("legal.privacy-policy-{$language}")->render()),
        ]);
    })->name('privacy-policy');

    Route::get('/form/{language}/{form:slug}', MarketingPublicForm::class)->name('marketing.forms.public');
    Route::get('/menu/{language}/{menu:slug}', PublicMenu::class)->name('menu.public');

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
            Route::get('/manage/contacts', ContactIndex::class);
            Route::get('/manage/leads', [LeadController::class, 'leadslist'])->name('leads.list');
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
            Route::get('/manage/bookings/create', BookingCreate::class)->name('bookings.create');
            Route::get('/manage/bookings/walkin', BookingWalkIn::class)->name('bookings.walk-in');
            Route::get('/manage/bookings/waitlist', BookingWaitlistIndex::class)->name('bookings.waitlist');
            Route::get('/manage/bookings/show/{booking}/{date?}', BookingShow::class)->name('bookings.show');
            Route::get('/manage/bookings/edit/{booking}', BookingEdit::class)->name('bookings.edit');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/manage/menu/products', ProductIndex::class)->name('menu.products');
            Route::get('/manage/menu/ingredients', IngredientIndex::class)->name('menu.ingredients');
            Route::get('/manage/menu/menus', MenuIndex::class)->name('menu.menus');
            Route::get('/manage/profile', ProfileEdit::class)->name('profile.edit');
            Route::get('/manage/settings', SettingsIndex::class)->name('settings.index');
            Route::get('/manage/settings/notification-modes', NotificationModesEdit::class)->name('settings.notification-modes');
            Route::get('/manage/settings/booking-remind-hours', BookingRemindHoursEdit::class)->name('settings.booking-remind-hours');
            Route::get('/manage/settings/max-sitting-time', MaxSittingTimeEdit::class)->name('settings.max-sitting-time');
            Route::get('/manage/settings/opening-hours', OpeningHoursEdit::class)->name('settings.opening-hours');
            Route::get('/manage/settings/pax-capacity', PaxCapacityEdit::class)->name('settings.pax-capacity');
            Route::get('/manage/settings/message-channel-cases', MessageChannelCasesIndex::class)->name('settings.message-channel-cases');
            Route::get('/manage/settings/messages', MessageTemplatesIndex::class)->name('settings.messages');
            Route::get('/manage/settings/rooms', RoomIndex::class)->name('settings.rooms');
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
