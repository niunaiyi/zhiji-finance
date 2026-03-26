<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tasks\Task;

class FindPeriodByYearMonthTask extends Task
{
    public function run(int $companyId, int $year, int $month): ?Period
    {
        return Period::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();
    }
}
