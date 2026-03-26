<?php

namespace App\Containers\Finance\AccountsReceivable\Actions;

use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Containers\Finance\AccountsReceivable\Models\ArReceipt;
use App\Containers\Finance\AccountsReceivable\Models\ArSettlement;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * 应收账款核销动作。
 * 将收款单与应收单据进行匹配核销，更新单据余额。
 */
class SettleArAction extends Action
{
    public function run(array $data)
    {
        $this->checkRole(['admin', 'accountant']);

        $bill    = ArBill::findOrFail($data['ar_bill_id']);
        $receipt = ArReceipt::findOrFail($data['ar_receipt_id']);
        $amount  = (float) $data['amount'];

        if ($amount > $bill->balance) {
            throw new \Exception('核销金额超过应收单据未结余额');
        }
        if ($amount > $receipt->balance) {
            throw new \Exception('核销金额超过收款单未结余额');
        }

        return DB::transaction(function () use ($bill, $receipt, $amount) {
            ArSettlement::create([
                'ar_bill_id'    => $bill->id,
                'ar_receipt_id' => $receipt->id,
                'amount'        => $amount,
                'settled_at'    => now(),
                'settled_by'    => auth()->id(),
            ]);

            // 更新应收单据状态
            $bill->increment('settled_amount', $amount);
            $bill->decrement('balance', $amount);
            $bill->status = $bill->balance <= 0 ? 'settled' : 'partial';
            $bill->save();

            // 更新收款单状态
            $receipt->increment('settled_amount', $amount);
            $receipt->decrement('balance', $amount);
            $receipt->status = $receipt->balance <= 0 ? 'settled' : 'partial';
            $receipt->save();
            
            return true;
        });
    }
}
