<?php

namespace App\Containers\Finance\GeneralLedger\Actions;

use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\GeneralLedger\Models\Balance;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class YearEndRolloverAction extends Action
{
    public function run(int $companyId, int $fiscalYear): void
    {
        $this->checkRole(['admin', 'accountant']);

        // 获取本年最后一个期间
        $lastPeriod = Period::where('company_id', $companyId)
            ->where('fiscal_year', $fiscalYear)
            ->where('period_number', 12)
            ->firstOrFail();

        if ($lastPeriod->status !== 'locked') {
            throw new \Exception('Last period must be locked before year-end rollover');
        }

        // 获取下一年第一个期间
        $nextYearFirstPeriod = Period::where('company_id', $companyId)
            ->where('fiscal_year', $fiscalYear + 1)
            ->where('period_number', 1)
            ->firstOrFail();

        DB::transaction(function () use ($companyId, $lastPeriod, $nextYearFirstPeriod) {
            // 获取本年期末余额
            $balances = Balance::where('company_id', $companyId)
                ->where('period_id', $lastPeriod->id)
                ->get();

            // 结转到下一年期初
            foreach ($balances as $balance) {
                Balance::create([
                    'company_id' => $companyId,
                    'period_id' => $nextYearFirstPeriod->id,
                    'account_id' => $balance->account_id,
                    'opening_debit' => $balance->closing_debit,
                    'opening_credit' => $balance->closing_credit,
                    'period_debit' => '0.00',
                    'period_credit' => '0.00',
                    'closing_debit' => $balance->closing_debit,
                    'closing_credit' => $balance->closing_credit,
                ]);
            }

            // TODO: 结转损益类科目到本年利润
        });
    }
}
