<?php

namespace App\Containers\Finance\Inventory\Actions;

use App\Containers\Finance\Inventory\Models\InventoryItem;
use App\Ship\Parents\Actions\Action;

class CreateInventoryItemAction extends Action
{
    public function run(array $data): InventoryItem
    {
        $this->checkRole(['admin', 'accountant']);

        return InventoryItem::create($data);
    }
}
