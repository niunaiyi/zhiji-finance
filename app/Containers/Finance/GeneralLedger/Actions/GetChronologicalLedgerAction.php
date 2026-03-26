<?php

namespace App\Containers\Finance\GeneralLedger\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;

class GetChronologicalLedgerAction extends Action
{
    public function run(int $companyId, int $periodId, ?string $startDate = null, ?string $endDate = null): array
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = Voucher::with(['lines.account'])
            ->where('company_id', $companyId)
            ->where('period_id', $periodId)
            ->where('status', 'posted');

        if ($startDate) {
            $query->where('voucher_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('voucher_date', '<=', $endDate);
        }

        $vouchers = $query->orderBy('voucher_date')
                          ->orderBy('voucher_no')
                          ->get();

        return $vouchers->map(function ($voucher) {
            return [
                'voucher_no' => $voucher->voucher_no,
                'voucher_date' => $voucher->voucher_date,
                'voucher_type' => $voucher->voucher_type,
                'summary' => $voucher->summary,
                'total_debit' => $voucher->total_debit,
                'total_credit' => $voucher->total_credit,
                'lines' => $voucher->lines->map(fn($line) => [
                    'account' => $line->account->name,
                    'summary' => $line->summary,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ])->toArray(),
            ];
        })->toArray();
    }
}
