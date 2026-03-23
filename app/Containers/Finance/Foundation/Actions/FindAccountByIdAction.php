<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Tasks\FindAccountByIdTask;
use App\Ship\Parents\Actions\Action;

class FindAccountByIdAction extends Action
{
    public function __construct(
        private readonly FindAccountByIdTask $findAccountByIdTask,
    ) {}

    public function run(int $id): Account
    {
        return $this->findAccountByIdTask->run($id);
    }
}
