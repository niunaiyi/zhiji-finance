<?php

namespace App\Containers\Finance\Voucher\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\GeneralLedger\Models\BalanceAux;
use App\Ship\Parents\Tasks\Task;

class UpdateBalanceAuxTask extends Task
{
    public function run(int $periodId, int $accountId, int $auxCategoryId, int $auxItemId, string $debit, string $credit): BalanceAux
    {
        $companyId = app('current.company_id');

        $balance = BalanceAux::firstOrCreate(
            [
                'company_id' => $companyId,
                'period_id' => $periodId,
                'account_id' => $accountId,
                'aux_category_id' => $auxCategoryId,
                'aux_item_id' => $auxItemId,
            ],
            [
                'opening_debit' => '0.00',
                'opening_credit' => '0.00',
                'period_debit' => '0.00',
                'period_credit' => '0.00',
                'closing_debit' => '0.00',
                'closing_credit' => '0.00',
            ]
        );

        $balance->period_debit = bcadd($balance->period_debit, $debit, 2);
        $balance->period_credit = bcadd($balance->period_credit, $credit, 2);

        // 重算期末余额
        $account = Account::find($accountId);
        if ($account->balance_direction === 'debit') {
            $balance->closing_debit = bcadd(
                bcsub($balance->opening_debit, $balance->opening_credit, 2),
                bcsub($balance->period_debit, $balance->period_credit, 2),
                2
            );
            $balance->closing_credit = '0.00';
        } else {
            $balance->closing_credit = bcadd(
                bcsub($balance->opening_credit, $balance->opening_debit, 2),
                bcsub($balance->period_credit, $balance->period_debit, 2),
                2
            );
            $balance->closing_debit = '0.00';
        }

        $balance->save();
        return $balance;
    }
}
