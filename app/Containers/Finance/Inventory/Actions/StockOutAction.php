<?php

namespace App\Containers\Finance\Inventory\Actions;

use App\Containers\Finance\Inventory\Models\InventoryItem;
use App\Containers\Finance\Inventory\Models\InventoryTransaction;
use App\Ship\Parents\Actions\Action;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByYearMonthTask;
use App\Containers\Finance\Voucher\Actions\GenerateVoucherFromBusinessAction;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StockOutAction extends Action
{
    public function run(int $companyId, array $data): InventoryItem
    {
        $this->checkRole(['admin', 'accountant']);

        return DB::transaction(function () use ($data, $companyId) {
            $item = InventoryItem::lockForUpdate()->find($data['item_id']);
            
            if ($item->current_quantity < $data['quantity']) {
                throw new HttpException(422, '库存不足');
            }

            $newQty = $item->current_quantity - $data['quantity'];
            
            $item->update([
                'current_quantity' => $newQty,
            ]);

            InventoryTransaction::create([
                'company_id' => $companyId,
                'trans_type' => 'sales_out',
                'inventory_id' => $item->id,
                'warehouse_id' => 0,
                'qty' => -$data['quantity'],
                'unit_cost' => $item->current_average_cost,
                'total_cost' => -($data['quantity'] * $item->current_average_cost),
                'trans_date' => $data['record_date'],
            ]);

            $totalCost = $data['quantity'] * $item->current_average_cost;
            if ($totalCost > 0) {
                 // Find period
                 $date = \Carbon\Carbon::parse($data['record_date']);
                 $period = app(FindPeriodByYearMonthTask::class)->run($companyId, $date->year, $date->month);
                 
                 if ($period) {
                     app(GenerateVoucherFromBusinessAction::class)->run('inventory_out', $item->id, [
                         'period_id'    => $period->id,
                         'voucher_date' => $data['record_date'],
                         'summary'      => "出库: {$item->name} / Qty: {$data['quantity']}",
                         'lines'        => [
                             [
                                 'account_id' => 70, // 6401 主营业务成本
                                 'debit'      => (string)$totalCost,
                                 'credit'     => '0.00',
                                 'summary'    => "出库: {$item->name}",
                             ],
                             [
                                 'account_id' => 14, // 1405 库存商品
                                 'debit'      => '0.00',
                                 'credit'     => (string)$totalCost,
                                 'summary'    => "出库: {$item->name}",
                             ]
                         ]
                     ]);
                 }
            }

            return $item;
        });
    }
}
