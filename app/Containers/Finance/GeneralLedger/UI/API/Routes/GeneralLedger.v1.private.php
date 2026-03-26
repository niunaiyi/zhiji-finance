<?php

use App\Containers\Finance\GeneralLedger\UI\API\Controllers\GeneralLedgerController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'tenant'], 'prefix' => 'general-ledger'], function () {
    Route::get('/balance-sheet', [GeneralLedgerController::class, 'balanceSheet']);
    Route::get('/detail-ledger', [GeneralLedgerController::class, 'detailLedger']);
    Route::get('/chronological', [GeneralLedgerController::class, 'chronological']);
    Route::get('/auxiliary-ledger', [GeneralLedgerController::class, 'auxiliaryLedger']);
});
