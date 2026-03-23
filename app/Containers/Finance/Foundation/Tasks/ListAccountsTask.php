<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAccountsTask extends Task
{
    public function run(array $filters = []): LengthAwarePaginator
    {
        $query = Account::query();

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate();
    }
}
