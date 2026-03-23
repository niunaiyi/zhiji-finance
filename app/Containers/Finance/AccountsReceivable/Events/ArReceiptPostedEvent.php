<?php

namespace App\Containers\Finance\AccountsReceivable\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ArReceiptPostedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $periodId,
        public int $receiptId,
        public int $customerId,
        public float $amount,
    ) {}
}
