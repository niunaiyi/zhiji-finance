<?php

namespace App\Containers\Finance\Auth\Data\Repositories;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Repositories\Repository;

class CompanyRepository extends Repository
{
    protected $fieldSearchable = [
        'code' => 'like',
        'name' => 'like',
        'status' => '=',
    ];

    public function model(): string
    {
        return Company::class;
    }
}
