<?php

use App\Containers\Finance\Inventory\UI\API\Controllers\InventoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
 * Inventory Items API
 *
 * Provides CRUD for inventory item master data (stored in inventory_items table or
 * a lightweight JSON store). Falls back gracefully when tables don't exist.
 *
 * Endpoints:
 *   GET  /api/v1/inventory-items         – list all items
 *   POST /api/v1/inventory-items         – create item
 *   POST /api/v1/inventory/stock-in      – record stock in
 *   POST /api/v1/inventory/stock-out     – record stock out
 */

Route::middleware(['auth:api', 'tenant'])->group(function () {

    // List all inventory items
    Route::get('inventory-items', [InventoryController::class, 'listItems']);

    // Create inventory item
    Route::post('inventory-items', [InventoryController::class, 'createItem']);

    // Stock In
    Route::post('inventory/stock-in', [InventoryController::class, 'stockIn']);

    // Stock Out
    Route::post('inventory/stock-out', [InventoryController::class, 'stockOut']);

    // Extended Inventory Balances (for future use)
    Route::get('inventory/balances', [InventoryController::class, 'getBalances']);
});
