<?php

namespace App\Containers\Finance\Voucher\Data\Repositories;

use App\Containers\Finance\Voucher\Models\VoucherLine;
use App\Ship\Parents\Repositories\Repository;

class VoucherLineRepository extends Repository
{
    protected $fieldSearchable = [
        'voucher_id' => '=',
        'account_id' => '=',
    ];

    public function model(): string
    {
        return VoucherLine::class;
    }
}
