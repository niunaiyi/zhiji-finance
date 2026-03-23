<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tasks\Task;

class FindPeriodByIdTask extends Task
{
    public function run(int $id): Period
    {
        return Period::findOrFail($id);
    }
}
