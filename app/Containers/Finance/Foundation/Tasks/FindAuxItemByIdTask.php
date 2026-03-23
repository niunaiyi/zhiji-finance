<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tasks\Task;

class FindAuxItemByIdTask extends Task
{
    public function run(int $id): AuxItem
    {
        return AuxItem::with(['auxCategory', 'parent', 'children'])->findOrFail($id);
    }
}
