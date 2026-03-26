<?php

use App\Containers\AppSection\Authentication\UI\API\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])
    ->name('api_authentication_login');
