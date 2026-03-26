<?php

namespace App\Containers\Finance\AccountsPayable\Actions;

use App\Containers\Finance\AccountsPayable\Models\ApPayment;
use App\Ship\Parents\Actions\Action;

class ListApPaymentsAction extends Action
{
    public function run(array $filters)
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = ApPayment::with(['supplier', 'period'])
            ->orderBy('payment_date', 'desc');

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->paginate($filters['limit'] ?? 20);
    }
}
