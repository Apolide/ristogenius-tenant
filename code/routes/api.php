<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Middleware\CheckSystemToken;

//use App\Http\Controllers\Api\NotificationController as ApiNotificationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => [CheckSystemToken::class]], function () {

      //Route::apiResource('products', ProductController::class);

    //   Route::get('products', [ProductController::class, 'index']);
    //   Route::get('products/{id}', [ProductController::class, 'show']);
    //   Route::get('products/search', [ProductController::class, 'search']);

    //   Route::post('companies/getfromvat', [CompanyController::class, 'getfromvat']);

    //   Route::get('customers/getbyphone/{phone}', [CustomerController::class, 'getbyphone']);
  
    #Customers
    //Route::get('/manage/customers', [App\Http\Controllers\CustomerController::class, 'customerslist']);
});

// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/notifications/unread', [ApiNotificationController::class, 'unread']);
// });