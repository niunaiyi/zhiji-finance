<?php

namespace App\Containers\Finance\Voucher\Data\Repositories;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Repositories\Repository;

class VoucherRepository extends Repository
{
    protected $fieldSearchable = [
        'voucher_type' => '=',
        'voucher_no' => 'like',
        'voucher_date' => '=',
        'status' => '=',
        'source_type' => '=',
        'period_id' => '=',
    ];

    public function model(): string
    {
        return Voucher::class;
    }
}
