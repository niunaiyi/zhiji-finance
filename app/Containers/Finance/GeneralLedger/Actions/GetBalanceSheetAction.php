<?php

namespace App\Containers\Finance\GeneralLedger\Actions;

use App\Containers\Finance\GeneralLedger\Models\Balance;
use App\Ship\Parents\Actions\Action;

class GetBalanceSheetAction extends Action
{
    public function run(int $companyId, int $periodId, ?int $accountId = null): array
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = Balance::with('account')
            ->where('company_id', $companyId)
            ->where('period_id', $periodId);

        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $balances = $query->get();

        // 构建层级结构
        return $this->buildHierarchy($balances);
    }

    private function buildHierarchy($balances): array
    {
        // 简化实现：直接返回扁平列表
        // 实际应该构建树形结构
        return $balances->map(function ($balance) {
            return [
                'account' => $balance->account,
                'opening_debit' => $balance->opening_debit,
                'opening_credit' => $balance->opening_credit,
                'period_debit' => $balance->period_debit,
                'period_credit' => $balance->period_credit,
                'closing_debit' => $balance->closing_debit,
                'closing_credit' => $balance->closing_credit,
            ];
        })->toArray();
    }
}
