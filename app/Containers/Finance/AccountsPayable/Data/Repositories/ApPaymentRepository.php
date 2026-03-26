<?php

namespace App\Containers\Finance\AccountsPayable\Data\Repositories;

use App\Containers\Finance\AccountsPayable\Models\ApPayment;
use App\Ship\Parents\Repositories\Repository;

class ApPaymentRepository extends Repository
{
    protected $fieldSearchable = [
        'payment_no' => 'like',
        'supplier_id' => '=',
        'status' => '=',
        'period_id' => '=',
    ];

    public function model(): string
    {
        return ApPayment::class;
    }
}
