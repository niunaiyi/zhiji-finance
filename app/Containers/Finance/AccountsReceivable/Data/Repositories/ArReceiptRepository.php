<?php

namespace App\Containers\Finance\AccountsReceivable\Data\Repositories;

use App\Containers\Finance\AccountsReceivable\Models\ArReceipt;
use App\Ship\Parents\Repositories\Repository;

class ArReceiptRepository extends Repository
{
    protected $fieldSearchable = [
        'receipt_no' => 'like',
        'customer_id' => '=',
        'status' => '=',
        'period_id' => '=',
    ];

    public function model(): string
    {
        return ArReceipt::class;
    }
}
