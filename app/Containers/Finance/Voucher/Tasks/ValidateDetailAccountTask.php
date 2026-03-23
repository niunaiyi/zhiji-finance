<?php

namespace App\Containers\Finance\Voucher\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;

class ValidateDetailAccountTask extends Task
{
    public function run(array $accountIds): bool
    {
        $nonDetailAccounts = Account::whereIn('id', $accountIds)
            ->where('is_detail', false)
            ->exists();

        return !$nonDetailAccounts;
    }
}
