<?php

namespace App\Containers\Finance\Inventory\Actions;

use App\Containers\Finance\Inventory\Models\Inventory;
use App\Containers\Finance\Inventory\Models\InventoryTransaction;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class RecordStockMovementAction extends Action
{
    /**
     * Records a stock movement and updates the inventory balance/cost.
     *
     * @param array $data [company_id, trans_type, inventory_id, warehouse_id, qty, unit_cost, source_type, source_id, trans_date]
     */
    public function run(array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($data) {
            $companyId = $data['company_id'];
            $inventoryId = $data['inventory_id'];
            $warehouseId = $data['warehouse_id'];
            $qty = (float) $data['qty'];
            $transType = $data['trans_type'];
            $transDate = $data['trans_date'] ?? now();

            // 1. Fetch current stock balance for this item in this warehouse
            $inventory = Inventory::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'inventory_id' => $inventoryId,
                    'warehouse_id' => $warehouseId,
                ],
                [
                    'qty' => 0,
                    'unit_cost' => 0,
                    'total_cost' => 0,
                ]
            );

            $currentQty = (float) $inventory->qty;
            $currentTotalCost = (float) $inventory->total_cost;
            $currentUnitCost = (float) $inventory->unit_cost;

            $transUnitCost = (float) ($data['unit_cost'] ?? 0);
            $transTotalCost = 0;

            if ($qty > 0) {
                // Inbound Case
                $transTotalCost = round($qty * $transUnitCost, 2);

                $newQty = $currentQty + $qty;
                $newTotalCost = $currentTotalCost + $transTotalCost;

                // Simple Weighted Average Cost Calculation
                $newUnitCost = $newQty > 0 ? ($newTotalCost / $newQty) : 0;

                $inventory->update([
                    'qty' => $newQty,
                    'unit_cost' => round($newUnitCost, 4),
                    'total_cost' => round($newTotalCost, 2),
                ]);
            } else {
                // Outbound Case (qty is negative in data? or passed as positive?)
                // Standard: trans_qty in transaction record is negative for outbound
                $absQty = abs($qty);
                $transUnitCost = $currentUnitCost; // Outbound uses current average cost
                $transTotalCost = -round($absQty * $transUnitCost, 2);

                $newQty = $currentQty + $qty; // e.g. 10 + (-2) = 8
                $newTotalCost = $currentTotalCost + $transTotalCost;

                // Outbound doesn't change unit cost in weighted average unless qty becomes 0
                $newUnitCost = $newQty > 0 ? $currentUnitCost : 0;

                $inventory->update([
                    'qty' => $newQty,
                    'total_cost' => round($newTotalCost, 2),
                    // unit_cost stays the same
                ]);
            }

            // 2. Create the transaction record
            return InventoryTransaction::create([
                'company_id' => $companyId,
                'trans_type' => $transType,
                'inventory_id' => $inventoryId,
                'warehouse_id' => $warehouseId,
                'qty' => $qty,
                'unit_cost' => round($transUnitCost, 4),
                'total_cost' => round($transTotalCost, 2),
                'source_type' => $data['source_type'] ?? null,
                'source_id' => $data['source_id'] ?? null,
                'trans_date' => $transDate,
            ]);
        });
    }
}
