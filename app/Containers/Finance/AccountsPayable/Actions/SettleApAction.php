<?php

namespace App\Containers\Finance\AccountsPayable\Actions;

use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Containers\Finance\AccountsPayable\Models\ApPayment;
use App\Containers\Finance\AccountsPayable\Models\ApSettlement;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * 应付账款核销动作。
 * 将付款单与应付单据进行匹配核销，更新双方余额及状态。
 */
class SettleApAction extends Action
{
    /**
     * 执行核销逻辑。
     *
     * @param array $data 包含 ap_bill_id, ap_payment_id, amount
     * @return bool
     * @throws \Exception
     */
    public function run(array $data)
    {
        $this->checkRole(['admin', 'accountant']);

        $bill    = ApBill::findOrFail($data['ap_bill_id']);
        $payment = ApPayment::findOrFail($data['ap_payment_id']);
        $amount  = (float) $data['amount'];

        if ($amount > $bill->balance) {
            throw new \Exception('核销金额超过应付单据未结余额');
        }
        if ($amount > $payment->balance) {
            throw new \Exception('核销金额超过付款单未结余额');
        }

        return DB::transaction(function () use ($bill, $payment, $amount) {
            ApSettlement::create([
                'ap_bill_id'    => $bill->id,
                'ap_payment_id' => $payment->id,
                'amount'        => $amount,
                'settled_at'    => now(),
                'settled_by'    => auth()->id(),
            ]);

            $bill->increment('settled_amount', $amount);
            $bill->decrement('balance', $amount);
            $bill->status = $bill->balance <= 0 ? 'settled' : 'partial';
            $bill->save();

            $payment->increment('settled_amount', $amount);
            $payment->decrement('balance', $amount);
            $payment->status = $payment->balance <= 0 ? 'settled' : 'partial';
            $payment->save();
            
            return true;
        });
    }
}
