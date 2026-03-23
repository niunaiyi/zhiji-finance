<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Tasks\CheckAuxItemHasChildrenTask;
use App\Ship\Parents\Actions\Action;

class DeactivateAuxItemAction extends Action
{
    public function __construct(
        private readonly CheckAuxItemHasChildrenTask $checkAuxItemHasChildrenTask,
    ) {}

    public function run(int $id): AuxItem
    {
        $this->checkAuxItemHasChildrenTask->run($id);

        $auxItem = AuxItem::findOrFail($id);
        $auxItem->update(['is_active' => false]);

        return $auxItem;
    }
}
