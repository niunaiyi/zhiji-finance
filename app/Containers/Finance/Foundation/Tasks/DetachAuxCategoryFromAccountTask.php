<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\AccountRepository;
use App\Ship\Parents\Tasks\Task;

class DetachAuxCategoryFromAccountTask extends Task
{
    public function __construct(
        private readonly AccountRepository $repository
    ) {}

    public function run(int $accountId, int $auxCategoryId): bool
    {
        $account = $this->repository->find($accountId);

        $account->auxCategories()->detach($auxCategoryId);

        return true;
    }
}
