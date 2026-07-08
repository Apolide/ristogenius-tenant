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
