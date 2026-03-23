<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Tasks\ListAuxItemsTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuxItemsAction extends Action
{
    public function __construct(
        private readonly ListAuxItemsTask $listAuxItemsTask,
    ) {}

    public function run(array $filters = []): LengthAwarePaginator
    {
        return $this->listAuxItemsTask->run($filters);
    }
}
