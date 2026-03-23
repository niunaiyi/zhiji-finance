<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Tasks\UpdateAuxItemTask;
use App\Ship\Parents\Actions\Action;

class UpdateAuxItemAction extends Action
{
    public function __construct(
        private readonly UpdateAuxItemTask $updateAuxItemTask,
    ) {}

    public function run(int $id, array $data): AuxItem
    {
        return $this->updateAuxItemTask->run($id, $data);
    }
}
