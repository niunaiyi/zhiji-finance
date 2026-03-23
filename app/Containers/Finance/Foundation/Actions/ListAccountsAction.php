<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Tasks\ListAccountsTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAccountsAction extends Action
{
    public function __construct(
        private readonly ListAccountsTask $listAccountsTask,
    ) {}

    public function run(array $filters = []): LengthAwarePaginator
    {
        return $this->listAccountsTask->run($filters);
    }
}
