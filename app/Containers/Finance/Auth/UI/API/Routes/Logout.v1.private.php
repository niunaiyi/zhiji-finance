<?php

use App\Containers\Finance\Auth\UI\API\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post('auth/logout', LogoutController::class);
