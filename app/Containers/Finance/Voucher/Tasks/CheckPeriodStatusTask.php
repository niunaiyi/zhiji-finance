<?php

namespace App\Containers\Finance\Voucher\Tasks;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tasks\Task;
use App\Ship\Exceptions\BusinessException;

class CheckPeriodStatusTask extends Task
{
    public function run(int $periodId): bool
    {
        $period = Period::find($periodId);

        if (!$period) {
            throw new BusinessException('Period not found');
        }

        if ($period->status === 'locked') {
            throw new BusinessException('Period is locked');
        }

        return true;
    }
}
