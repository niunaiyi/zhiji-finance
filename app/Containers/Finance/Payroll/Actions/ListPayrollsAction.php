<?php

namespace App\Containers\Finance\Payroll\Actions;

use App\Containers\Finance\Payroll\Models\Payroll;
use App\Ship\Parents\Actions\Action;
use Illuminate\Database\Eloquent\Collection;

class ListPayrollsAction extends Action
{
    public function run(int $companyId): Collection
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        return Payroll::where('company_id', $companyId)
            ->with(['period', 'lines.employee'])
            ->get();
    }
}
