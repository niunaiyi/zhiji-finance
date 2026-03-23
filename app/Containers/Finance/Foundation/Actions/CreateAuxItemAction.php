<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Tasks\CreateAuxItemTask;
use App\Ship\Parents\Actions\Action;

class CreateAuxItemAction extends Action
{
    public function __construct(
        private readonly CreateAuxItemTask $createAuxItemTask,
    ) {}

    public function run(array $data): AuxItem
    {
        return $this->createAuxItemTask->run($data);
    }
}
