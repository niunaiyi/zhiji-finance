<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Ship\Parents\Actions\Action;
use Illuminate\Pagination\LengthAwarePaginator;

class ListVouchersAction extends Action
{
    public function run(array $filters): LengthAwarePaginator
    {
        $this->checkRole(['admin', 'accountant', 'auditor', 'viewer']);

        $query = Voucher::query()->with(['period', 'creator']);

        if (!empty($filters['period_id'])) {
            $query->byPeriod($filters['period_id']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['voucher_type'])) {
            $query->byType($filters['voucher_type']);
        }

        if (!empty($filters['keyword'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('voucher_no', 'like', '%' . $filters['keyword'] . '%')
                  ->orWhere('summary', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        $query->orderBy('voucher_date', 'desc')
              ->orderBy('voucher_no', 'desc');

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
