<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;

class GetVoucherAction extends Action
{
    public function run(int $voucherId): Voucher
    {
        return Voucher::with([
            'lines.account',
            'lines.auxItems.category',
            'lines.auxItems.item',
            'period',
            'creator',
            'reviewer',
            'poster',
        ])->findOrFail($voucherId);
    }
}
