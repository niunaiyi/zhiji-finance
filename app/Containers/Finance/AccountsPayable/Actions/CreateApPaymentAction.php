<?php

namespace App\Containers\Finance\AccountsPayable\Actions;

use App\Containers\Finance\AccountsPayable\Models\ApPayment;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class CreateApPaymentAction extends Action
{
    public function run(array $data): ApPayment
    {
        $this->checkRole(['admin', 'accountant']);

        return DB::transaction(function () use ($data) {
            $payment = ApPayment::create($data);
            
            // TODO: 未来实现自动对冲 ApBill 的逻辑
            
            return $payment;
        });
    }
}
