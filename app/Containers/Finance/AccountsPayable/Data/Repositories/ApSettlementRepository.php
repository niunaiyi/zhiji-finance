<?php

namespace App\Containers\Finance\AccountsPayable\Data\Repositories;

use App\Containers\Finance\AccountsPayable\Models\ApSettlement;
use App\Ship\Parents\Repositories\Repository;

class ApSettlementRepository extends Repository
{
    protected $fieldSearchable = [
        'ap_bill_id' => '=',
        'ap_payment_id' => '=',
    ];

    public function model(): string
    {
        return ApSettlement::class;
    }
}
