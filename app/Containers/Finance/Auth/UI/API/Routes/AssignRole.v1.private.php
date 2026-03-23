<?php

use App\Containers\Finance\Auth\UI\API\Controllers\AssignRoleController;
use Illuminate\Support\Facades\Route;

Route::post('auth/companies/roles', AssignRoleController::class)
    ->middleware(['auth:api'])
    ->name('api_auth_assign_role');
