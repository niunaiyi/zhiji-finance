<?php

namespace App\Containers\Finance\AccountsReceivable\Data\Repositories;

use App\Containers\Finance\AccountsReceivable\Models\ArSettlement;
use App\Ship\Parents\Repositories\Repository;

class ArSettlementRepository extends Repository
{
    protected $fieldSearchable = [
        'ar_bill_id' => '=',
        'ar_receipt_id' => '=',
    ];

    public function model(): string
    {
        return ArSettlement::class;
    }
}
