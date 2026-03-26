<?php

namespace App\Containers\Finance\Inventory\Actions;

use App\Containers\Finance\Inventory\Models\InventoryItem;
use App\Containers\Finance\Inventory\Models\InventoryTransaction;
use App\Ship\Parents\Actions\Action;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByYearMonthTask;
use App\Containers\Finance\Voucher\Actions\GenerateVoucherFromBusinessAction;
use Illuminate\Support\Facades\DB;

class StockInAction extends Action
{
    public function run(int $companyId, array $data): InventoryItem
    {
        $this->checkRole(['admin', 'accountant']);

        return DB::transaction(function () use ($data, $companyId) {
            $item = InventoryItem::lockForUpdate()->find($data['item_id']);
            
            $newQty = $item->current_quantity + $data['quantity'];
            $oldTotalValue = $item->current_quantity * $item->current_average_cost;
            $newTotalValue = $oldTotalValue + ($data['quantity'] * $data['unit_price']);
            $newAvgCost = $newQty > 0 ? $newTotalValue / $newQty : 0;

            $item->update([
                'current_quantity' => $newQty,
                'current_average_cost' => $newAvgCost,
            ]);

            InventoryTransaction::create([
                'company_id' => $companyId,
                'trans_type' => 'purchase_in',
                'inventory_id' => $item->id,
                'warehouse_id' => 0,
                'qty' => $data['quantity'],
                'unit_cost' => $data['unit_price'],
                'total_cost' => $data['quantity'] * $data['unit_price'],
                'trans_date' => $data['record_date'],
            ]);

            $totalAmount = $data['quantity'] * $data['unit_price'];
            if ($totalAmount > 0) {
                // Find period
                $date = \Carbon\Carbon::parse($data['record_date']);
                $period = app(FindPeriodByYearMonthTask::class)->run($companyId, $date->year, $date->month);
                
                if ($period) {
                    app(GenerateVoucherFromBusinessAction::class)->run('inventory_in', $item->id, [
                        'period_id'    => $period->id,
                        'voucher_date' => $data['record_date'],
                        'summary'      => "入库: {$item->name} / Qty: {$data['quantity']}",
                        'lines'        => [
                            [
                                'account_id' => 14, // 1405 库存商品
                                'debit'      => (string)$totalAmount,
                                'credit'     => '0.00',
                                'summary'    => "入库: {$item->name}",
                            ],
                            [
                                'account_id' => 42, // 2202 应付账款
                                'debit'      => '0.00',
                                'credit'     => (string)$totalAmount,
                                'summary'    => "入库: {$item->name}",
                            ]
                        ]
                    ]);
                }
            }

            return $item;
        });
    }
}
