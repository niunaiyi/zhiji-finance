<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Tasks\ListAuxCategoriesTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuxCategoriesAction extends Action
{
    public function __construct(
        private readonly ListAuxCategoriesTask $listAuxCategoriesTask,
    ) {}

    public function run(array $filters = []): LengthAwarePaginator
    {
        return $this->listAuxCategoriesTask->run($filters);
    }
}
