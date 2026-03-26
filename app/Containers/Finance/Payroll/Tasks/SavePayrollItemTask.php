<?php

namespace App\Containers\Finance\Payroll\Tasks;

use App\Containers\Finance\Payroll\Models\PayrollItem;
use App\Ship\Parents\Tasks\Task;

class SavePayrollItemTask extends Task
{
    public function run(array $data): PayrollItem
    {
        return PayrollItem::updateOrCreate(['id' => $data['id'] ?? null], $data);
    }
}
