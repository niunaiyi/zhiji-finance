<?php

use App\Containers\Finance\Voucher\UI\API\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware(['auth:api'])->group(function () {
    Route::prefix('vouchers')->group(function () {
        Route::get('/', [VoucherController::class, 'index']);
        Route::post('/', [VoucherController::class, 'store']);
        Route::get('/{id}', [VoucherController::class, 'show']);
        Route::post('/{id}/review', [VoucherController::class, 'review']);
        Route::post('/{id}/post', [VoucherController::class, 'post']);
        Route::post('/{id}/reverse', [VoucherController::class, 'reverse']);
        Route::post('/{id}/void', [VoucherController::class, 'void']);
    });
});
