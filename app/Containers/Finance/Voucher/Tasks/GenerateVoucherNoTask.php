<?php

namespace App\Containers\Finance\Voucher\Tasks;

use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Tasks\Task;

class GenerateVoucherNoTask extends Task
{
    public function run(int $companyId, int $periodId, string $voucherType): string
    {
        $period = Period::find($periodId);
        $year = $period->fiscal_year;

        $typeCode = match($voucherType) {
            'receipt' => '收',
            'payment' => '付',
            'transfer' => '记',
        };

        $maxNo = Voucher::where('company_id', $companyId)
            ->where('period_id', $periodId)
            ->where('voucher_type', $voucherType)
            ->max('voucher_no');

        if ($maxNo) {
            preg_match('/(\d+)$/', $maxNo, $matches);
            $nextSeq = intval($matches[1]) + 1;
        } else {
            $nextSeq = 1;
        }

        return sprintf('%d-%s-%04d', $year, $typeCode, $nextSeq);
    }
}
