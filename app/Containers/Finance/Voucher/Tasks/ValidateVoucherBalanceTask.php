<?php

namespace App\Containers\Finance\Voucher\Tasks;

use App\Ship\Parents\Tasks\Task;

class ValidateVoucherBalanceTask extends Task
{
    public function run(array $lines): bool
    {
        $totalDebit = '0.00';
        $totalCredit = '0.00';

        foreach ($lines as $line) {
            $totalDebit = bcadd($totalDebit, $line['debit'] ?? '0.00', 2);
            $totalCredit = bcadd($totalCredit, $line['credit'] ?? '0.00', 2);
        }

        return bccomp($totalDebit, $totalCredit, 2) === 0;
    }
}
