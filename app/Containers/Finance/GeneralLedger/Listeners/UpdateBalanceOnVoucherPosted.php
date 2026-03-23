<?php

namespace App\Containers\Finance\GeneralLedger\Listeners;

use App\Containers\Finance\Voucher\Events\VoucherPostedEvent;
use App\Containers\Finance\Voucher\Tasks\UpdateBalanceAuxTask;
use App\Containers\Finance\Voucher\Tasks\UpdateBalanceTask;

class UpdateBalanceOnVoucherPosted
{
    public function handle(VoucherPostedEvent $event): void
    {
        foreach ($event->lines as $line) {
            // 更新科目余额
            app(UpdateBalanceTask::class)->run(
                $event->periodId,
                $line['account_id'],
                $line['debit'],
                $line['credit']
            );

            // 更新辅助核算余额
            if (!empty($line['aux_items'])) {
                foreach ($line['aux_items'] as $auxItem) {
                    app(UpdateBalanceAuxTask::class)->run(
                        $event->periodId,
                        $line['account_id'],
                        $auxItem['aux_category_id'],
                        $auxItem['aux_item_id'],
                        $line['debit'],
                        $line['credit']
                    );
                }
            }
        }
    }
}
