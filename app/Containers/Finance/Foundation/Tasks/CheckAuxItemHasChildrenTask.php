<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Validation\ValidationException;

class CheckAuxItemHasChildrenTask extends Task
{
    public function run(int $auxItemId): void
    {
        $hasActiveChildren = AuxItem::where('parent_id', $auxItemId)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveChildren) {
            throw ValidationException::withMessages([
                'aux_item' => 'Cannot deactivate aux item with active children'
            ]);
        }
    }
}
