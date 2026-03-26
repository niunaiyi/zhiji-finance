<?php

use App\Containers\Finance\Purchase\UI\API\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'purchase'], function () {
    // 采购订单
    Route::get('/orders', [PurchaseController::class, 'indexOrders']);
    Route::post('/orders', [PurchaseController::class, 'storeOrder']);
    Route::get('/orders/{id}', [PurchaseController::class, 'showOrder']);

    // 采购收料
    Route::get('/receipts', [PurchaseController::class, 'indexReceipts']);
    Route::post('/receipts', [PurchaseController::class, 'storeReceipt']);

    // 采购发票
    Route::get('/invoices', [PurchaseController::class, 'indexInvoices']);
    Route::post('/invoices', [PurchaseController::class, 'storeInvoice']);
});
