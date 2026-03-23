<?php

use App\Containers\Finance\Auth\UI\API\Controllers\ListCompaniesController;
use Illuminate\Support\Facades\Route;

Route::get('auth/companies', ListCompaniesController::class)
    ->middleware(['auth:api'])
    ->name('api_auth_list_companies');
