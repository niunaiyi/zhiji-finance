<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Tasks\UpdateAccountTask;
use App\Ship\Parents\Actions\Action;

class UpdateAccountAction extends Action
{
    public function __construct(
        private readonly UpdateAccountTask $updateAccountTask,
    ) {}

    public function run(int $id, array $data): Account
    {
        return $this->updateAccountTask->run($id, $data);
    }
}
