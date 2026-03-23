<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPeriodsTask extends Task
{
    public function run(array $filters = []): LengthAwarePaginator
    {
        $query = Period::query();

        if (isset($filters['fiscal_year'])) {
            $query->where('fiscal_year', $filters['fiscal_year']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('fiscal_year', 'desc')
            ->orderBy('period_number', 'asc')
            ->paginate($filters['limit'] ?? 15);
    }
}
