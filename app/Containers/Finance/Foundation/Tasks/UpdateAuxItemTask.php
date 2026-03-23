<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Arr;

class UpdateAuxItemTask extends Task
{
    public function run(int $id, array $data): AuxItem
    {
        $auxItem = AuxItem::findOrFail($id);

        // Only allow updating name, parent_id, is_active, and extra
        // code and aux_category_id are immutable
        $auxItem->update(Arr::only($data, ['name', 'parent_id', 'is_active', 'extra']));

        return $auxItem->fresh();
    }
}
