<?php

namespace App\Containers\Finance\Foundation\Data\Repositories;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Repositories\Repository;

class AccountRepository extends Repository
{
    protected $fieldSearchable = [
        'code' => 'like',
        'name' => 'like',
        'element_type' => '=',
        'is_active' => '=',
        'is_detail' => '=',
    ];

    public function model(): string
    {
        return Account::class;
    }
}
