<?php

namespace App\Containers\Finance\GeneralLedger\Actions;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class ClosePeriodAction extends Action
{
    public function run(int $periodId): Period
    {
        $period = Period::findOrFail($periodId);

        if ($period->status !== 'open') {
            throw new \Exception('Period is not open');
        }

        // TODO: 检查所有子模块是否已结账
        // - InventoryPeriodClosedEvent
        // - ArPeriodClosedEvent
        // - ApPeriodClosedEvent
        // - FixedAssetPeriodClosedEvent
        // - PayrollPeriodClosedEvent

        return DB::transaction(function () use ($period) {
            // 锁定期间
            $period->update([
                'status' => 'locked',
                'closed_at' => now(),
            ]);

            // TODO: 抛出 PeriodLockedEvent

            return $period->fresh();
        });
    }
}
