<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Tasks\ListPeriodsTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPeriodsAction extends Action
{
    public function __construct(
        private readonly ListPeriodsTask $listPeriodsTask,
    ) {}

    public function run(array $filters = []): LengthAwarePaginator
    {
        return $this->listPeriodsTask->run($filters);
    }
}
