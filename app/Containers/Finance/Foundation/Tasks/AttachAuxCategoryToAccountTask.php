<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;

class AttachAuxCategoryToAccountTask extends Task
{
    public function run(Account $account, int $auxCategoryId, array $pivotData = []): bool
    {
        // Use syncWithoutDetaching to prevent duplicate pivot records
        $account->auxCategories()->syncWithoutDetaching([
            $auxCategoryId => $pivotData
        ]);

        return true;
    }
}
