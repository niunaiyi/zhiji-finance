<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\CreateAccountController;
use Illuminate\Support\Facades\Route;

Route::post('accounts', CreateAccountController::class)
    ->middleware(['auth:api', 'tenant'])
    ->name('api_foundation_create_account');
