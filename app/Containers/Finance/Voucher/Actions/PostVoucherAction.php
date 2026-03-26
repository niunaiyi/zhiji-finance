<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Events\VoucherPostedEvent;
use App\Containers\Finance\Voucher\Models\Voucher;
use App\Containers\Finance\Voucher\Tasks\CheckPeriodStatusTask;
use App\Containers\Finance\Voucher\Tasks\ValidateDetailAccountTask;
use App\Containers\Finance\Voucher\Tasks\ValidateVoucherBalanceTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class PostVoucherAction extends Action
{
    public function run(int $voucherId): Voucher
    {
        $this->checkRole(['admin', 'auditor']);

        $voucher = Voucher::with('lines.auxItems')->findOrFail($voucherId);

        if ($voucher->status !== 'reviewed') {
            throw new \Exception('Only reviewed vouchers can be posted');
        }

        // 校验期间状态
        app(CheckPeriodStatusTask::class)->run($voucher->period_id);

        // 校验借贷平衡
        $lines = $voucher->lines->map(fn($line) => [
            'debit' => $line->debit,
            'credit' => $line->credit,
        ])->toArray();

        if (!app(ValidateVoucherBalanceTask::class)->run($lines)) {
            throw new \Exception('Voucher is not balanced');
        }

        // 校验末级科目
        $accountIds = $voucher->lines->pluck('account_id')->toArray();
        if (!app(ValidateDetailAccountTask::class)->run($accountIds)) {
            throw new \Exception('Only detail accounts can be used');
        }

        return DB::transaction(function () use ($voucher) {
            // 更新凭证状态
            $voucher->update([
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // 准备事件数据
            $eventLines = $voucher->lines->map(function ($line) {
                return [
                    'account_id' => $line->account_id,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'aux_items' => $line->auxItems->map(fn($aux) => [
                        'aux_category_id' => $aux->aux_category_id,
                        'aux_item_id' => $aux->aux_item_id,
                    ])->toArray(),
                ];
            })->toArray();

            // 抛出事件
            event(new VoucherPostedEvent(
                $voucher->id,
                $voucher->company_id,
                $voucher->period_id,
                $eventLines
            ));

            return $voucher->fresh();
        });
    }
}
