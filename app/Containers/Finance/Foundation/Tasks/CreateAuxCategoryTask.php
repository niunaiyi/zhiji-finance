<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tasks\Task;

class CreateAuxCategoryTask extends Task
{
    public function run(array $data): AuxCategory
    {
        // Remove is_system from data if present (security: users cannot set this)
        unset($data['is_system']);

        // Create the category using mass assignment (only fillable fields)
        $auxCategory = AuxCategory::create($data);

        // Manually set is_system to false for security (bypasses mass assignment protection)
        // This ensures users cannot create system categories
        $auxCategory->is_system = false;
        $auxCategory->save();

        return $auxCategory;
    }
}
