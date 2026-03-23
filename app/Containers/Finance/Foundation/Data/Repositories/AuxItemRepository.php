<?php

namespace App\Containers\Finance\Foundation\Data\Repositories;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Repositories\Repository;

class AuxItemRepository extends Repository
{
    protected $fieldSearchable = [
        'code' => 'like',
        'name' => 'like',
        'aux_category_id' => '=',
        'is_active' => '=',
    ];

    public function model(): string
    {
        return AuxItem::class;
    }
}
