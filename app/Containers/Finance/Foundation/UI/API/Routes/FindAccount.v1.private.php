<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\FindAccountController;
use Illuminate\Support\Facades\Route;

Route::get('accounts/{id}', FindAccountController::class)
    ->middleware(['auth:api', 'tenant'])
    ->name('api_foundation_find_account');
