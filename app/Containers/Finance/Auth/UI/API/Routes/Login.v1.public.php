<?php

use App\Containers\Finance\Auth\UI\API\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', LoginController::class);
