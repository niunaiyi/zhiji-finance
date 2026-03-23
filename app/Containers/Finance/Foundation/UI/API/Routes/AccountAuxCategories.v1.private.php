<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\AttachAuxCategoryToAccountController;
use App\Containers\Finance\Foundation\UI\API\Controllers\DetachAuxCategoryFromAccountController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:api', 'tenant'])
    ->group(function () {
        Route::post('accounts/{account_id}/aux-categories', AttachAuxCategoryToAccountController::class);
        Route::delete('accounts/{account_id}/aux-categories/{aux_category_id}', DetachAuxCategoryFromAccountController::class);
    });
