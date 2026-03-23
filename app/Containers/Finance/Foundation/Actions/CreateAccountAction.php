<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Tasks\CalculateAccountLevelTask;
use App\Containers\Finance\Foundation\Tasks\CreateAccountTask;
use App\Containers\Finance\Foundation\Tasks\ValidateAccountCodeTask;
use App\Ship\Parents\Actions\Action;

class CreateAccountAction extends Action
{
    public function __construct(
        private readonly ValidateAccountCodeTask $validateAccountCodeTask,
        private readonly CalculateAccountLevelTask $calculateAccountLevelTask,
        private readonly CreateAccountTask $createAccountTask,
    ) {}

    public function run(array $data): Account
    {
        $this->validateAccountCodeTask->run($data['code']);

        $calculated = $this->calculateAccountLevelTask->run(
            $data['parent_id'] ?? null,
            $data['code']
        );

        return $this->createAccountTask->run(array_merge($data, $calculated));
    }
}
