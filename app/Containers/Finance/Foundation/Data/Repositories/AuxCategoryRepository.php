<?php

namespace App\Containers\Finance\Foundation\Data\Repositories;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Repositories\Repository;

class AuxCategoryRepository extends Repository
{
    protected $fieldSearchable = [
        'code' => 'like',
        'name' => 'like',
        'is_system' => '=',
    ];

    public function model(): string
    {
        return AuxCategory::class;
    }
}
