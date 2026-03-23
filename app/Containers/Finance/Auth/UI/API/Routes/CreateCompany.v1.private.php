<?php

use App\Containers\Finance\Auth\UI\API\Controllers\CreateCompanyController;
use Illuminate\Support\Facades\Route;

Route::post('auth/companies', CreateCompanyController::class)
    ->middleware(['auth:api'])
    ->name('api_auth_create_company');
