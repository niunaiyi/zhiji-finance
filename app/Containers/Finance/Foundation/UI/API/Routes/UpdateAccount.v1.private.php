<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\UpdateAccountController;
use Illuminate\Support\Facades\Route;

Route::patch('accounts/{id}', UpdateAccountController::class)
    ->middleware(['auth:api', 'tenant'])
    ->name('api_foundation_update_account');
