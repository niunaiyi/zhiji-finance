<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Validation\ValidationException;

class ValidateAccountHasNoChildrenTask extends Task
{
    public function run(int $accountId): void
    {
        $hasChildren = Account::where('parent_id', $accountId)->exists();

        if ($hasChildren) {
            throw ValidationException::withMessages([
                'account' => 'Cannot deactivate account with children'
            ]);
        }
    }
}
