<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\ClosePeriodController;
use App\Containers\Finance\Foundation\UI\API\Controllers\CreatePeriodController;
use App\Containers\Finance\Foundation\UI\API\Controllers\FindPeriodController;
use App\Containers\Finance\Foundation\UI\API\Controllers\InitializeFiscalYearController;
use App\Containers\Finance\Foundation\UI\API\Controllers\ListPeriodsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:api', 'tenant'])
    ->group(function () {
        Route::post('periods', CreatePeriodController::class);
        Route::get('periods', ListPeriodsController::class);
        Route::get('periods/{id}', FindPeriodController::class);
        Route::post('periods/{id}/close', ClosePeriodController::class);
        Route::post('fiscal-years/initialize', InitializeFiscalYearController::class);
    });
