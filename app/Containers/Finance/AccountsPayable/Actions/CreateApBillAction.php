<?php

namespace App\Containers\Finance\AccountsPayable\Actions;

use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByYearMonthTask;
use App\Containers\Finance\Voucher\Actions\GenerateVoucherFromBusinessAction;
use App\Ship\Parents\Actions\Action;

class CreateApBillAction extends Action
{
    public function run(array $data): ApBill
    {
        $this->checkRole(['admin', 'accountant']);

        $data['settled_amount'] = 0;
        $data['balance']        = $data['amount'];
        $data['status']         = 'open';
        $data['is_estimate']    = $data['is_estimate'] ?? false;

        $bill = ApBill::create($data);

        // Generate Voucher
        $date = \Carbon\Carbon::parse($bill->bill_date);
        $period = app(FindPeriodByYearMonthTask::class)->run($bill->company_id, $date->year, $date->month);
        
        if ($period) {
            app(GenerateVoucherFromBusinessAction::class)->run('ap_bill', $bill->id, [
                'period_id'    => $period->id,
                'voucher_date' => $bill->bill_date,
                'summary'      => "应付账款计提: {$bill->bill_no}",
                'lines'        => [
                    [
                        'account_id' => 74, // 6602 管理费用 (简化逻辑，应根据业务类型映射)
                        'debit'      => (string)$bill->amount,
                        'credit'     => '0.00',
                        'summary'    => "费用: {$bill->bill_no}",
                    ],
                    [
                        'account_id' => 42, // 2202 应付账款
                        'debit'      => '0.00',
                        'credit'     => (string)$bill->amount,
                        'summary'    => "应付: {$bill->bill_no}",
                    ]
                ]
            ]);
        }

        return $bill;
    }
}
