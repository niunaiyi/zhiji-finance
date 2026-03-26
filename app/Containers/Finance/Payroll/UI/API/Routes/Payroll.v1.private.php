<?php

use App\Containers\Finance\Payroll\UI\API\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'payroll'], function () {
    Route::get('/items', [PayrollController::class, 'listItems']);
    Route::post('/items', [PayrollController::class, 'saveItem']);
    Route::get('/', [PayrollController::class, 'listPayrolls']);
    Route::post('/calculate', [PayrollController::class, 'calculate']);
});
