<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Arr;

class UpdateAccountTask extends Task
{
    public function run(int $id, array $data): Account
    {
        $account = Account::findOrFail($id);

        // Only allow updating name and is_active
        $account->update(Arr::only($data, ['name', 'is_active']));

        return $account->fresh();
    }
}
