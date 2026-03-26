<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByIdTask;
use App\Ship\Parents\Actions\Action;

class FindPeriodByIdAction extends Action
{
    public function __construct(
        private readonly FindPeriodByIdTask $findPeriodByIdTask,
    ) {}

    public function run(int $id): Period
    {
        return $this->findPeriodByIdTask->run($id);
    }
}
