<?php

namespace App\Containers\Finance\Payroll\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayrollPostedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $periodId,
        public int $payrollId,
        public float $totalAmount,
        public array $deptSummary,  // [{dept_id, amount}]
    ) {}
}
