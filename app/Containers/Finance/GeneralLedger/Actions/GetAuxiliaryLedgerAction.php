<?php

namespace App\Containers\Finance\GeneralLedger\Actions;

use App\Containers\Finance\GeneralLedger\Models\BalanceAux;
use App\Ship\Parents\Actions\Action;

class GetAuxiliaryLedgerAction extends Action
{
    public function run(int $companyId, int $periodId, int $auxCategoryId, ?int $auxItemId = null): array
    {
        $query = BalanceAux::with(['account', 'category', 'item'])
            ->where('company_id', $companyId)
            ->where('period_id', $periodId)
            ->where('aux_category_id', $auxCategoryId);

        if ($auxItemId) {
            $query->where('aux_item_id', $auxItemId);
        }

        $balances = $query->get();

        return $balances->map(function ($balance) {
            return [
                'account' => $balance->account,
                'aux_category' => $balance->category,
                'aux_item' => $balance->item,
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
