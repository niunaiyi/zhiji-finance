<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\AccountRepository;
use App\Ship\Parents\Tasks\Task;

class AttachAuxCategoryToAccountTask extends Task
{
    public function __construct(
        private readonly AccountRepository $repository
    ) {}

    public function run(int $accountId, int $auxCategoryId, array $pivotData = []): bool
    {
        $account = $this->repository->find($accountId);

        // Use sync with false to prevent detaching existing relationships
        $account->auxCategories()->attach($auxCategoryId, $pivotData);

        return true;
    }
}
