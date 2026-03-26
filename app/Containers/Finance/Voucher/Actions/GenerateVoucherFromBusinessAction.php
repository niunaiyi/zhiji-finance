<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;

class GenerateVoucherFromBusinessAction extends Action
{
    /**
     * @param string $sourceType e.g., 'ap_bill', 'ar_receipt', 'inventory_in', 'payroll'
     * @param int $sourceId
     * @param array $params {
     *   period_id: int,
     *   voucher_date: string,
     *   summary: string,
     *   lines: array [
     *     { account_id: int, debit: string, credit: string, summary: string, aux_items: array }
     *   ]
     * }
     */
    public function run(string $sourceType, int $sourceId, array $params): Voucher
    {
        // 1. 组装数据
        $data = [
            'period_id'    => $params['period_id'],
            'voucher_type' => $params['voucher_type'] ?? '记',
            'voucher_date' => $params['voucher_date'],
            'summary'      => $params['summary'],
            'source_type'  => $sourceType,
            'source_id'    => $sourceId,
            'lines'        => $params['lines'],
        ];

        // 2. 调用现有的 CreateVoucherAction
        return app(CreateVoucherAction::class)->run($data);
    }
}
