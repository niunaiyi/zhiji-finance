<?php

namespace App\Containers\Finance\GeneralLedger\Data\Repositories;

use App\Containers\Finance\GeneralLedger\Models\Balance;
use App\Ship\Parents\Repositories\Repository;

class BalanceRepository extends Repository
{
    protected $fieldSearchable = [
        'period_id' => '=',
        'account_id' => '=',
    ];

    public function model(): string
    {
        return Balance::class;
    }
}
