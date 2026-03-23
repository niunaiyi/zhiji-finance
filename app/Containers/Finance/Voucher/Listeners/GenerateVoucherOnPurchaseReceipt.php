<?php

namespace App\Containers\Finance\Voucher\Listeners;

use App\Containers\Finance\Purchase\Events\PurchaseReceiptPostedEvent;
use App\Containers\Finance\Voucher\Actions\CreateVoucherAction;

class GenerateVoucherOnPurchaseReceipt
{
    public function handle(PurchaseReceiptPostedEvent $event): void
    {
        // 生成凭证：借:库存商品 / 贷:应付账款
        $lines = [];

        foreach ($event->lines as $line) {
            $lines[] = [
                'account_id' => 1, // 库存商品科目ID（需要从配置获取）
                'summary' => '采购入库',
                'debit' => $line['total_cost'],
                'credit' => '0.00',
            ];
        }

        $lines[] = [
            'account_id' => 2, // 应付账款科目ID（需要从配置获取）
            'summary' => '采购入库',
            'debit' => '0.00',
            'credit' => $event->totalAmount,
            'aux_items' => [
                ['aux_category_id' => 2, 'aux_item_id' => $event->supplierId]
            ],
        ];

        app(CreateVoucherAction::class)->run([
            'period_id' => $event->periodId,
            'voucher_type' => 'transfer',
            'voucher_date' => now()->toDateString(),
            'summary' => '采购入库自动生成',
            'source_type' => 'purchase',
            'source_id' => $event->receiptId,
            'lines' => $lines,
        ]);
    }
}
