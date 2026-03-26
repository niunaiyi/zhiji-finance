<?php

namespace App\Containers\Finance\AccountsReceivable\Actions;

use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByYearMonthTask;
use App\Containers\Finance\Voucher\Actions\GenerateVoucherFromBusinessAction;
use App\Ship\Parents\Actions\Action;

class CreateArBillAction extends Action
{
    public function run(array $data): ArBill
    {
        $this->checkRole(['admin', 'accountant']);

        $data['settled_amount'] = 0;
        $data['balance']        = $data['amount'];
        $data['status']         = 'open';

        $bill = ArBill::create($data);

        // Generate Voucher
        $date = \Carbon\Carbon::parse($bill->bill_date);
        $period = app(FindPeriodByYearMonthTask::class)->run($bill->company_id, $date->year, $date->month);
        
        if ($period) {
            app(GenerateVoucherFromBusinessAction::class)->run('ar_bill', $bill->id, [
                'period_id'    => $period->id,
                'voucher_date' => $bill->bill_date,
                'summary'      => "应收账款计提: {$bill->bill_no}",
                'lines'        => [
                    [
                        'account_id' => 6, // 1122 应收账款
                        'debit'      => (string)$bill->amount,
                        'credit'     => '0.00',
                        'summary'    => "应收: {$bill->bill_no}",
                    ],
                    [
                        'account_id' => 65, // 6001 主营业务收入
                        'debit'      => '0.00',
                        'credit'     => (string)$bill->amount,
                        'summary'    => "收入: {$bill->bill_no}",
                    ]
                ]
            ]);
        }

        return $bill;
    }
}
