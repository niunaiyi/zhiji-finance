<?php

namespace App\Containers\Finance\AccountsPayable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApPaymentPostedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $periodId,
        public int $paymentId,
        public int $supplierId,
        public float $amount,
    ) {}
}
