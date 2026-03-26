<?php

use App\Containers\Finance\AccountsPayable\UI\API\Controllers\ApController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'ap'], function () {
    // AP Bills
    Route::get('/bills', [ApController::class, 'indexBills']);
    Route::post('/bills', [ApController::class, 'storeBill']);
    Route::get('/bills/{id}', [ApController::class, 'showBill']);

    // AP Payments
    Route::get('/payments', [ApController::class, 'indexPayments']);
    Route::post('/payments', [ApController::class, 'storePayment']);

    // Settlement
    Route::post('/settle', [ApController::class, 'settle']);
});
