<?php

namespace App\Containers\Finance\AccountsReceivable\Actions;

use App\Containers\Finance\AccountsReceivable\Models\ArReceipt;
use App\Ship\Parents\Actions\Action;

class ListArReceiptsAction extends Action
{
    public function run(array $filters)
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = ArReceipt::with(['customer', 'period'])
            ->orderBy('receipt_date', 'desc');

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($filters['limit'] ?? 20);
    }
}
