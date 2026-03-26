<?php

namespace App\Containers\Finance\Payroll\Actions;

use App\Containers\Finance\Payroll\Models\PayrollItem;
use App\Containers\Finance\Payroll\Tasks\SavePayrollItemTask;
use App\Ship\Parents\Actions\Action;

class SavePayrollItemAction extends Action
{
    public function run(array $data): PayrollItem
    {
        $this->checkRole(['admin', 'accountant']);

        return app(SavePayrollItemTask::class)->run($data);
    }
}
