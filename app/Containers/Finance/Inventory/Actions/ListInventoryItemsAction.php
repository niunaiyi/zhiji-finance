<?php

namespace App\Containers\Finance\Inventory\Actions;

use App\Containers\Finance\Inventory\Tasks\GetAllInventoryItemsTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class ListInventoryItemsAction extends Action
{
    public function run(int $companyId): Collection
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        return app(GetAllInventoryItemsTask::class)->run($companyId);
    }
}
