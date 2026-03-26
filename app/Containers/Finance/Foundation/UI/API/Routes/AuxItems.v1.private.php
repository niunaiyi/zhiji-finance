<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\CreateAuxItemController;
use App\Containers\Finance\Foundation\UI\API\Controllers\DeactivateAuxItemController;
use App\Containers\Finance\Foundation\UI\API\Controllers\FindAuxItemController;
use App\Containers\Finance\Foundation\UI\API\Controllers\ListAuxItemsController;
use App\Containers\Finance\Foundation\UI\API\Controllers\UpdateAuxItemController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'tenant'])->group(function () {
    Route::post('aux-items', CreateAuxItemController::class);
    Route::patch('aux-items/{id}', UpdateAuxItemController::class);
    Route::post('aux-items/{id}/deactivate', DeactivateAuxItemController::class);
    Route::get('aux-items', ListAuxItemsController::class);
    Route::get('aux-items/{id}', FindAuxItemController::class);
});

