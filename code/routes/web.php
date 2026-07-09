<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadsController;

// Livewire
use App\Livewire\Contacts\ContactIndex;
use App\Livewire\Profile\ProfileEdit;
use App\Livewire\Settings\BookingRemindHoursEdit;
use App\Livewire\Settings\Departments\DepartmentIndex;
use App\Livewire\Settings\MaxSittingTimeEdit;
use App\Livewire\Settings\MessageChannelCasesIndex;
use App\Livewire\Settings\MessageTemplatesIndex;
use App\Livewire\Settings\NotificationModesEdit;
use App\Livewire\Settings\OpeningHoursEdit;
use App\Livewire\Settings\PaxCapacityEdit;
use App\Livewire\Settings\RoomTables\RoomTableIndex;
use App\Livewire\Settings\Rooms\RoomIndex;
use App\Livewire\Settings\Rooms\RoomTablePlanner;
use App\Livewire\Settings\SettingsIndex;
use App\Livewire\Users\Userlist;


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


    Route::group(['middleware' => ['auth:sanctum', 'role:admin']], function () {
        // Contacts (customers/clients/interested people)
        Route::get('/manage/contacts', ContactIndex::class);

        // Tenant company profile
        Route::get('/manage/profile', ProfileEdit::class)->name('profile.edit');

        // Tenant settings
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
        Route::get('/rooms/{room}', RoomTablePlanner::class)->name('rooms.planner');
        Route::get('/manage/settimgs/departments', DepartmentIndex::class)->name('settings.departments');

        // Users (agents/employees)
        Route::get('/manage/users', Userlist::class)->name('users.index');
        Route::get('/manage/users/create', [UserController::class, 'usercreate'])->name('users.create');
        Route::get('/manage/users/edit/{id}', [UserController::class, 'useredit'])->name('users.edit');

        // Leads
        Route::get('/manage/leads', [LeadController::class, 'leadslist'])->name('leads.list');
    });



    Route::middleware(['auth'])->group(function () {
        Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');
    });

}); 
