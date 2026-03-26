<?php

use App\Containers\AppSection\Authentication\UI\API\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('logout', [AuthController::class, 'logout'])
    ->name('api_authentication_logout')
    ->middleware(['auth:api']);

Route::post('refresh', [AuthController::class, 'refresh'])
    ->name('api_authentication_refresh')
    ->middleware(['auth:api']);
