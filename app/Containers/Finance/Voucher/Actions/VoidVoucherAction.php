<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;

class VoidVoucherAction extends Action
{
    public function run(int $voucherId): Voucher
    {
        $this->checkRole(['admin', 'accountant']);

        $voucher = Voucher::findOrFail($voucherId);

        if (!in_array($voucher->status, ['draft', 'reviewed'])) {
            throw new \Exception('Only draft or reviewed vouchers can be voided');
        }

        $voucher->update(['status' => 'voided']);

        return $voucher->fresh();
    }
}
