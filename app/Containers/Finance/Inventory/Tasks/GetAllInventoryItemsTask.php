<?php

namespace App\Containers\Finance\Inventory\Tasks;

use App\Containers\Finance\Inventory\Models\InventoryItem;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class GetAllInventoryItemsTask extends Task
{
    public function run(int $companyId): Collection
    {
        return InventoryItem::where('company_id', $companyId)->get();
    }
}
