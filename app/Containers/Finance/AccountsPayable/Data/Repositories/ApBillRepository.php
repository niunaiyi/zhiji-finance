<?php

namespace App\Containers\Finance\AccountsPayable\Data\Repositories;

use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Ship\Parents\Repositories\Repository;

class ApBillRepository extends Repository
{
    protected $fieldSearchable = [
        'bill_no' => 'like',
        'supplier_id' => '=',
        'status' => '=',
        'is_estimate' => '=',
        'period_id' => '=',
    ];

    public function model(): string
    {
        return ApBill::class;
    }
}
