<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Tasks\ValidateAccountHasNoChildrenTask;
use App\Ship\Parents\Actions\Action;

class DeactivateAccountAction extends Action
{
    public function __construct(
        private readonly ValidateAccountHasNoChildrenTask $validateAccountHasNoChildrenTask,
    ) {}

    public function run(int $id): Account
    {
        $this->validateAccountHasNoChildrenTask->run($id);

        $account = Account::findOrFail($id);
        $account->update(['is_active' => false]);

        return $account;
    }
}
