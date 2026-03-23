<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuxItemsTask extends Task
{
    public function run(array $filters = []): LengthAwarePaginator
    {
        $query = AuxItem::query()->with(['auxCategory', 'parent']);

        foreach ($filters as $field => $value) {
            if ($field === 'search') {
                $query->where(function ($q) use ($value) {
                    $q->where('code', 'like', "%{$value}%")
                      ->orWhere('name', 'like', "%{$value}%");
                });
            } else {
                $query->where($field, $value);
            }
        }

        return $query->paginate($filters['limit'] ?? 15);
    }
}
