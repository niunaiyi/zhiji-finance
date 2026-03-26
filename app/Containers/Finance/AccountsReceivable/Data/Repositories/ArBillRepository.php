<?php

namespace App\Containers\Finance\AccountsReceivable\Data\Repositories;

use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Ship\Parents\Repositories\Repository;

class ArBillRepository extends Repository
{
    protected $fieldSearchable = [
        'bill_no' => 'like',
        'customer_id' => '=',
        'status' => '=',
        'period_id' => '=',
    ];

    public function model(): string
    {
        return ArBill::class;
    }
}
