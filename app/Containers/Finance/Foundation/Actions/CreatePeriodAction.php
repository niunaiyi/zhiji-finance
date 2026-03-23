<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\Foundation\Tasks\CreatePeriodTask;
use App\Containers\Finance\Foundation\Tasks\ValidatePeriodOverlapTask;
use App\Ship\Parents\Actions\Action;

class CreatePeriodAction extends Action
{
    public function __construct(
        private readonly ValidatePeriodOverlapTask $validatePeriodOverlapTask,
        private readonly CreatePeriodTask $createPeriodTask,
    ) {}

    public function run(array $data): Period
    {
        // Validate that the period doesn't overlap with existing periods
        $this->validatePeriodOverlapTask->run(
            $data['fiscal_year'],
            $data['start_date'],
            $data['end_date']
        );

        // Create the period
        return $this->createPeriodTask->run($data);
    }
}
