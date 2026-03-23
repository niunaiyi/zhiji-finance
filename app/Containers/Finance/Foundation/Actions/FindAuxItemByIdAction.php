<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Tasks\FindAuxItemByIdTask;
use App\Ship\Parents\Actions\Action;

class FindAuxItemByIdAction extends Action
{
    public function __construct(
        private readonly FindAuxItemByIdTask $findAuxItemByIdTask,
    ) {}

    public function run(int $id): AuxItem
    {
        return $this->findAuxItemByIdTask->run($id);
    }
}
