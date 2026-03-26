<?php

use App\Containers\Finance\GeneralLedger\UI\API\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'reports'], function () {
    Route::get('/aging-analysis', [ReportController::class, 'agingAnalysis']);
});
