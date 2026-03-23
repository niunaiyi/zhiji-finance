<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Tasks\DetachAuxCategoryFromAccountTask;
use App\Ship\Parents\Actions\Action;

class DetachAuxCategoryFromAccountAction extends Action
{
    public function __construct(
        private readonly DetachAuxCategoryFromAccountTask $detachAuxCategoryFromAccountTask,
    ) {}

    public function run(array $data): bool
    {
        return $this->detachAuxCategoryFromAccountTask->run(
            $data['account_id'],
            $data['aux_category_id']
        );
    }
}
