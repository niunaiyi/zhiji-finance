<?php

namespace App\Containers\Finance\Foundation\Data\Repositories;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Repositories\Repository;

class PeriodRepository extends Repository
{
    protected $fieldSearchable = [
        'fiscal_year' => '=',
        'period_number' => '=',
        'status' => '=',
    ];

    public function model(): string
    {
        return Period::class;
    }
}
