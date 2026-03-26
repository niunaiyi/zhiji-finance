<?php

use App\Containers\Finance\FixedAsset\UI\API\Controllers\FixedAssetController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'fixed-assets'], function () {
    Route::get('/', [FixedAssetController::class, 'listAssets']);
    Route::post('/', [FixedAssetController::class, 'createAsset']);
    Route::patch('/{id}', [FixedAssetController::class, 'updateAsset']);
    Route::delete('/{id}', [FixedAssetController::class, 'deleteAsset']);
    Route::post('/calculate-depreciation', [FixedAssetController::class, 'calculateDepreciation']);
    Route::post('/generate-voucher', [FixedAssetController::class, 'generateVoucher']);
});
