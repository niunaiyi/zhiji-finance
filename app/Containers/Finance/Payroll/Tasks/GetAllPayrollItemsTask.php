<?php

namespace App\Containers\Finance\Payroll\Tasks;

use App\Containers\Finance\Payroll\Models\PayrollItem;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class GetAllPayrollItemsTask extends Task
{
    public function run(int $companyId): Collection
    {
        return PayrollItem::where('company_id', $companyId)->get();
    }
}
