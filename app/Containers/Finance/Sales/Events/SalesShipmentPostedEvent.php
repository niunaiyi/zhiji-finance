<?php

namespace App\Containers\Finance\Sales\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalesShipmentPostedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $periodId,
        public int $shipmentId,
        public int $customerId,
        public float $saleAmount,
        public float $costAmount,
    ) {}
}
