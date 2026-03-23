<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuxCategoriesTask extends Task
{
    public function run(array $filters = []): LengthAwarePaginator
    {
        $query = AuxCategory::query();

        // Handle search parameter (format: "field:value")
        if (isset($filters['search'])) {
            $searchParts = explode(':', $filters['search'], 2);
            if (count($searchParts) === 2) {
                [$field, $value] = $searchParts;
                $query->where($field, $value);
            }
        }

        // Handle other direct filters
        foreach ($filters as $field => $value) {
            if ($field !== 'search') {
                $query->where($field, $value);
            }
        }

        return $query->orderBy('code')->paginate();
    }
}
