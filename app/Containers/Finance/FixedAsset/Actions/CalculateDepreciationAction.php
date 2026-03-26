<?php

namespace App\Containers\Finance\FixedAsset\Actions;

use App\Containers\Finance\FixedAsset\Models\DepreciationSchedule;
use App\Containers\Finance\FixedAsset\Models\FixedAsset;
use App\Ship\Parents\Actions\Action;
use App\Containers\Finance\Voucher\Actions\GenerateVoucherFromBusinessAction;
use Illuminate\Support\Facades\DB;

/**
 * 计提固定资产折旧动作。
 * 遍历所有在用资产，根据其原值、预计净残值和使用年限计算当月折旧额。
 */
class CalculateDepreciationAction extends Action
{
    /**
     * Calculates and records depreciation for all active assets in a given period.
     */
    public function run(int $companyId, int $periodId): array
    {
        $this->checkRole(['admin', 'accountant']);

        return DB::transaction(function () use ($companyId, $periodId) {
            $assets = FixedAsset::where('company_id', $companyId)
                ->where('status', 'active')
                ->get();

            $results = [];

            foreach ($assets as $asset) {
                // Check if already depreciated for this period
                $exists = DepreciationSchedule::where('fixed_asset_id', $asset->id)
                    ->where('period_id', $periodId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                // If net value is already <= residual value, stop depreciating
                $residualValue = round($asset->original_value * $asset->residual_rate, 2);
                if ($asset->net_value <= $residualValue) {
                    continue;
                }

                $depreciationAmount = 0;

                if ($asset->depreciation_method === 'straight_line') {
                    if ($asset->useful_life_months > 0) {
                        $depreciationAmount = round(($asset->original_value - $residualValue) / $asset->useful_life_months, 2);
                    }
                }

                // Ensure we don't depreciate below residual value
                if ($asset->net_value - $depreciationAmount < $residualValue) {
                    $depreciationAmount = $asset->net_value - $residualValue;
                }

                if ($depreciationAmount > 0) {
                    // 1. Create schedule
                    DepreciationSchedule::create([
                        'company_id' => $companyId,
                        'fixed_asset_id' => $asset->id,
                        'period_id' => $periodId,
                        'depreciation_amount' => $depreciationAmount,
                        'is_posted' => false,
                    ]);

                    // 2. Update asset accumulated depreciation and net value
                    $asset->increment('accumulated_depreciation', $depreciationAmount);
                    $asset->decrement('net_value', $depreciationAmount);

                    $results[] = [
                        'asset_id' => $asset->id,
                        'asset_name' => $asset->name,
                        'amount' => $depreciationAmount,
                    ];
                }
            }

            // 3. 生成会计凭证
            $totalAmount = array_sum(array_column($results, 'amount'));
            if ($totalAmount > 0) {
                app(GenerateVoucherFromBusinessAction::class)->run('fixed_asset_depreciation', $periodId, [
                    'period_id'    => $periodId,
                    'voucher_date' => now()->format('Y-m-d'), // 或期间最后一天
                    'summary'      => "计提固定资产折旧",
                    'lines'        => [
                        [
                            'account_id' => 74, // 6602 管理费用 - 折旧费
                            'debit'      => (string)$totalAmount,
                            'credit'     => '0.00',
                            'summary'    => "计提本期固定资产折旧",
                        ],
                        [
                            'account_id' => 28, // 1602 累计折旧
                            'debit'      => '0.00',
                            'credit'     => (string)$totalAmount,
                            'summary'    => "计提本期固定资产折旧",
                        ]
                    ]
                ]);
            }

            return $results;
        });
    }
}
