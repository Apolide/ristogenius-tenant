<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\ContactController;

// Livewire
use App\Livewire\Contacts\ContactIndex;



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

        
        #Contacts
        Route::get('/manage/contacts', ContactIndex::class);
      });
}); 

