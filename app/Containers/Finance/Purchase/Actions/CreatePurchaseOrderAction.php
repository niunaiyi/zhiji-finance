<?php

namespace App\Containers\Finance\Purchase\Actions;

use App\Containers\Finance\Purchase\Models\PurchaseOrder;
use App\Ship\Parents\Actions\Action;

class CreatePurchaseOrderAction extends Action
{
    public function run(array $data): PurchaseOrder
    {
        $this->checkRole(['admin', 'accountant']);
        
        $data['company_id'] = auth()->user()->current_company_id;
        $data['status'] = 'draft';

        return PurchaseOrder::create($data);
    }
}
