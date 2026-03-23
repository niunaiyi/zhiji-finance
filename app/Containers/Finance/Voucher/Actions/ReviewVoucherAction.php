<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;

class ReviewVoucherAction extends Action
{
    public function run(int $voucherId): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->status !== 'draft') {
            throw new \Exception('Only draft vouchers can be reviewed');
        }

        $voucher->update([
            'status' => 'reviewed',
            'reviewed_by' => auth()->id(),
        ]);

        return $voucher->fresh();
    }
}
