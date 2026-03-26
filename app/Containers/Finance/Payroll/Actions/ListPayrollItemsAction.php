<?php

namespace App\Containers\Finance\Payroll\Actions;

use App\Containers\Finance\Payroll\Tasks\GetAllPayrollItemsTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class ListPayrollItemsAction extends Action
{
    public function run(int $companyId): Collection
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        return app(GetAllPayrollItemsTask::class)->run($companyId);
    }
}
