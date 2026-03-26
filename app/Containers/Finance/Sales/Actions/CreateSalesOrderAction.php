<?php

namespace App\Containers\Finance\Sales\Actions;

use App\Containers\Finance\Sales\Models\SalesOrder;
use App\Ship\Parents\Actions\Action;

class CreateSalesOrderAction extends Action
{
    public function run(array $data): SalesOrder
    {
        $this->checkRole(['admin', 'accountant']);
        
        $data['company_id'] = auth()->user()->current_company_id;
        $data['status'] = 'draft';

        return SalesOrder::create($data);
    }
}
