<?php

use App\Containers\Finance\Foundation\UI\API\Controllers\CreateAuxCategoryController;
use App\Containers\Finance\Foundation\UI\API\Controllers\ListAuxCategoriesController;
use App\Containers\Finance\Foundation\UI\API\Controllers\UpdateAuxCategoryController;
use Illuminate\Support\Facades\Route;

// 辅助核算类别 (Auxiliary Categories)
Route::middleware(['auth:api', 'tenant'])->group(function () {
    Route::post('aux-categories', CreateAuxCategoryController::class);
    Route::patch('aux-categories/{id}', UpdateAuxCategoryController::class);
    Route::get('aux-categories', ListAuxCategoriesController::class);
});

