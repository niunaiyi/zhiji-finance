<?php

namespace App\Containers\Finance\Inventory\UI\API\Controllers;

use App\Containers\Finance\Inventory\Actions\RecordStockMovementAction;
use App\Containers\Finance\Inventory\Models\Inventory;
use App\Containers\Finance\Inventory\Models\InventoryItem;
use App\Containers\Finance\Inventory\Models\InventoryTransaction;
use App\Containers\Finance\Inventory\Actions\CreateInventoryItemAction;
use App\Containers\Finance\Inventory\Actions\ListInventoryItemsAction;
use App\Containers\Finance\Inventory\Actions\StockInAction;
use App\Containers\Finance\Inventory\Actions\StockOutAction;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends ApiController
{
    /**
     * List all inventory items.
     */
    public function listItems(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        $items = app(ListInventoryItemsAction::class)->run($companyId);

        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * Create an inventory item.
     */
    public function createItem(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        
        $data = $request->validate([
            'sku' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'unit' => 'nullable|string|max:20',
            'category' => 'nullable|string|max:50',
        ]);

        $data['company_id'] = $companyId;
        $item = app(CreateInventoryItemAction::class)->run($data);

        return response()->json([
            'data' => $item,
        ], 201);
    }

    /**
     * Record an inbound stock movement (Stock In).
     */
    public function stockIn(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        
        $data = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_price' => 'required|numeric|min:0',
            'record_date' => 'required|date',
        ]);

        $item = app(StockInAction::class)->run($companyId, $data);

        return response()->json([
            'message' => '入库成功',
            'data' => $item
        ]);
    }

    /**
     * Record an outbound stock movement (Stock Out).
     */
    public function stockOut(Request $request): JsonResponse
    {
        $companyId = (int)$request->header('X-Company-Id');
        
        $data = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|numeric|min:0.0001',
            'record_date' => 'required|date',
        ]);

        $item = app(StockOutAction::class)->run($companyId, $data);

        return response()->json([
            'message' => '出库成功',
            'data' => $item
        ]);
    }
}
