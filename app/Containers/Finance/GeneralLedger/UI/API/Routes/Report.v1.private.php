<?php

use App\Containers\Finance\GeneralLedger\UI\API\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'reports'], function () {
    Route::get('/aging', [ReportController::class, 'agingAnalysis']);
    Route::get('/trial-balance', [ReportController::class, 'trialBalance']);
    Route::get('/balance-sheet', [ReportController::class, 'balanceSheet']);
    Route::get('/income-statement', [ReportController::class, 'incomeStatement']);
});
