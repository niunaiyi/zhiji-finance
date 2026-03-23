<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\ListAccountsController;
use Illuminate\Support\Facades\Route;

Route::get('accounts', ListAccountsController::class)
    ->middleware(['auth:api', 'tenant'])
    ->name('api_foundation_list_accounts');
