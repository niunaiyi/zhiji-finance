<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\PeriodRepository;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tasks\Task;

class CreatePeriodTask extends Task
{
    public function __construct(
        private readonly PeriodRepository $repository
    ) {}

    public function run(array $data): Period
    {
        return $this->repository->create($data);
    }
}
