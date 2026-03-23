<?php

namespace App\Containers\Finance\GeneralLedger\Actions;

use App\Containers\Finance\Voucher\Models\VoucherLine;
use App\Ship\Parents\Actions\Action;

class GetDetailLedgerAction extends Action
{
    public function run(int $companyId, int $periodId, int $accountId): array
    {
        $lines = VoucherLine::with(['voucher', 'account'])
            ->where('company_id', $companyId)
            ->where('account_id', $accountId)
            ->whereHas('voucher', function ($query) use ($periodId) {
                $query->where('period_id', $periodId)
                      ->where('status', 'posted');
            })
            ->orderBy('created_at')
            ->get();

        return $lines->map(function ($line) {
            return [
                'voucher_no' => $line->voucher->voucher_no,
                'voucher_date' => $line->voucher->voucher_date,
                'summary' => $line->summary,
                'debit' => $line->debit,
                'credit' => $line->credit,
            ];
        })->toArray();
    }
}
