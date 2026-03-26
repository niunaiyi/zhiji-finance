<?php

use App\Containers\Finance\Sales\UI\API\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'sales'], function () {
    // Sales Orders
    Route::get('/orders', [SalesController::class, 'indexOrders']);
    Route::post('/orders', [SalesController::class, 'storeOrder']);
    Route::get('/orders/{id}', [SalesController::class, 'showOrder']);

    // Sales Shipments
    Route::get('/shipments', [SalesController::class, 'indexShipments']);
    Route::post('/shipments', [SalesController::class, 'storeShipment']);

    // Sales Invoices
    Route::get('/invoices', [SalesController::class, 'indexInvoices']);
    Route::post('/invoices', [SalesController::class, 'storeInvoice']);
});
