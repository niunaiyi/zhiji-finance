<?php

namespace App\Containers\Finance\AccountsReceivable\Actions;

use App\Containers\Finance\AccountsReceivable\Models\ArBill;
use App\Ship\Parents\Actions\Action;

class ListArBillsAction extends Action
{
    public function run(array $filters)
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = ArBill::with(['customer', 'period'])
            ->orderBy('bill_date', 'desc');

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['limit'] ?? 20);
    }
}
