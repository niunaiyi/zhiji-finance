<?php

use App\Containers\Finance\Auth\UI\API\Controllers\SelectCompanyController;
use Illuminate\Support\Facades\Route;

Route::post('auth/select-company', SelectCompanyController::class)->middleware(['auth:api']);
