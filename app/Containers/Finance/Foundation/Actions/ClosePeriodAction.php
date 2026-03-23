<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\Foundation\Tasks\ClosePeriodTask;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByIdTask;
use App\Ship\Parents\Actions\Action;

class ClosePeriodAction extends Action
{
    public function __construct(
        private readonly FindPeriodByIdTask $findPeriodByIdTask,
        private readonly ClosePeriodTask $closePeriodTask,
    ) {}

    public function run(int $periodId): Period
    {
        // Find the period (will throw ModelNotFoundException if not found or wrong company)
        $period = $this->findPeriodByIdTask->run($periodId);

        // Close the period (Period model will validate status transition and set closed_at/closed_by)
        return $this->closePeriodTask->run($period);
    }
}
