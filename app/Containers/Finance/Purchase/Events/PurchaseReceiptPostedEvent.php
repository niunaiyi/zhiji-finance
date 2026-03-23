<?php

namespace App\Containers\Finance\Purchase\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseReceiptPostedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $periodId,
        public int $receiptId,
        public int $supplierId,
        public array $lines,  // [{inventory_id, qty, unit_cost, total_cost}]
        public float $totalAmount,
    ) {}
}
