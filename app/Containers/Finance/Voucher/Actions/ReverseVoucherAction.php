<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class ReverseVoucherAction extends Action
{
    public function run(int $voucherId): Voucher
    {
        $this->checkRole(['admin', 'auditor']);

        $originalVoucher = Voucher::with('lines.auxItems')->findOrFail($voucherId);

        if ($originalVoucher->status !== 'posted') {
            throw new \Exception('Only posted vouchers can be reversed');
        }

        return DB::transaction(function () use ($originalVoucher) {
            // 标记原凭证为已红冲
            $originalVoucher->update(['status' => 'reversed']);

            // 创建反向凭证数据
            $reverseData = [
                'period_id' => $originalVoucher->period_id,
                'voucher_type' => $originalVoucher->voucher_type,
                'voucher_date' => now()->toDateString(),
                'summary' => '红冲：' . $originalVoucher->summary,
                'source_type' => 'reverse',
                'source_id' => $originalVoucher->id,
                'lines' => $originalVoucher->lines->map(function ($line) {
                    return [
                        'account_id' => $line->account_id,
                        'summary' => $line->summary,
                        'debit' => $line->credit, // 借贷互换
                        'credit' => $line->debit,
                        'aux_items' => $line->auxItems->map(fn($aux) => [
                            'aux_category_id' => $aux->aux_category_id,
                            'aux_item_id' => $aux->aux_item_id,
                        ])->toArray(),
                    ];
                })->toArray(),
            ];

            // 创建红冲凭证
            $reverseVoucher = app(CreateVoucherAction::class)->run($reverseData);

            // 自动审核并过账
            app(ReviewVoucherAction::class)->run($reverseVoucher->id);
            app(PostVoucherAction::class)->run($reverseVoucher->id);

            return $reverseVoucher->fresh();
        });
    }
}
