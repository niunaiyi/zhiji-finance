<?php

namespace App\Containers\Finance\AccountsReceivable\Actions;

use App\Containers\Finance\AccountsReceivable\Models\ArReceipt;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class CreateArReceiptAction extends Action
{
    public function run(array $data): ArReceipt
    {
        $this->checkRole(['admin', 'accountant']);

        return DB::transaction(function () use ($data) {
            $receipt = ArReceipt::create($data);
            
            // TODO: 未来实现自动对冲 ArBill 的逻辑
            
            return $receipt;
        });
    }
}
