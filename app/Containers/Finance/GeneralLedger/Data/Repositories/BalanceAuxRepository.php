<?php

namespace App\Containers\Finance\GeneralLedger\Data\Repositories;

use App\Containers\Finance\GeneralLedger\Models\BalanceAux;
use App\Ship\Parents\Repositories\Repository;

class BalanceAuxRepository extends Repository
{
    protected $fieldSearchable = [
        'period_id' => '=',
        'account_id' => '=',
        'aux_category_id' => '=',
        'aux_item_id' => '=',
    ];

    public function model(): string
    {
        return BalanceAux::class;
    }
}
