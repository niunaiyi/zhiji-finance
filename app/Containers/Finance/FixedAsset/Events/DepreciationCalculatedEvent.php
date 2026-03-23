<?php

namespace App\Containers\Finance\FixedAsset\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepreciationCalculatedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $companyId,
        public int $periodId,
        public array $lines,  // [{asset_id, dept_id, amount}]
    ) {}
}
