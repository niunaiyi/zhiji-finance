<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;

class FindAccountByIdTask extends Task
{
    public function run(int $id): Account
    {
        return Account::with(['parent', 'children'])->findOrFail($id);
    }
}
