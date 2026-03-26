<?php

use App\Containers\Finance\AccountsReceivable\UI\API\Controllers\ArController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'ar'], function () {
    // AR Bills
    Route::get('/bills', [ArController::class, 'indexBills']);
    Route::post('/bills', [ArController::class, 'storeBill']);
    Route::get('/bills/{id}', [ArController::class, 'showBill']);

    // AR Receipts
    Route::get('/receipts', [ArController::class, 'indexReceipts']);
    Route::post('/receipts', [ArController::class, 'storeReceipt']);

    // Settlement
    Route::post('/settle', [ArController::class, 'settle']);
});
