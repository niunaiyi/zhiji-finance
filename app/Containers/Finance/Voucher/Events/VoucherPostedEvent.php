<?php

namespace App\Containers\Finance\Voucher\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherPostedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $voucherId,
        public int $companyId,
        public int $periodId,
        public array $lines  // [{account_id, debit, credit, aux_items[]}]
    ) {}
}
