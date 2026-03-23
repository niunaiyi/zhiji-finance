<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\DeactivateAccountController;
use Illuminate\Support\Facades\Route;

Route::post('accounts/{id}/deactivate', DeactivateAccountController::class)
    ->middleware(['auth:api', 'tenant'])
    ->name('api_foundation_deactivate_account');
