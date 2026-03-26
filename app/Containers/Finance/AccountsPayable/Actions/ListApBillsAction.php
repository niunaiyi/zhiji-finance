<?php

namespace App\Containers\Finance\AccountsPayable\Actions;

use App\Containers\Finance\AccountsPayable\Models\ApBill;
use App\Ship\Parents\Actions\Action;

class ListApBillsAction extends Action
{
    public function run(array $filters)
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = ApBill::with(['supplier', 'period'])
            ->orderBy('bill_date', 'desc');

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['is_estimate'])) {
            $query->where('is_estimate', (bool) $filters['is_estimate']);
        }

        return $query->paginate($filters['limit'] ?? 20);
    }
}
