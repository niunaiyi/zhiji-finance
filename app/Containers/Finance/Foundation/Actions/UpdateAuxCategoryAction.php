<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Tasks\FindAuxCategoryByIdTask;
use App\Containers\Finance\Foundation\Tasks\UpdateAuxCategoryTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Validation\ValidationException;

class UpdateAuxCategoryAction extends Action
{
    public function __construct(
        private readonly FindAuxCategoryByIdTask $findAuxCategoryByIdTask,
        private readonly UpdateAuxCategoryTask $updateAuxCategoryTask,
    ) {}

    public function run(int $id, array $data): AuxCategory
    {
        $auxCategory = $this->findAuxCategoryByIdTask->run($id);

        if ($auxCategory->is_system) {
            throw ValidationException::withMessages([
                'is_system' => ['System categories cannot be modified'],
            ]);
        }

        return $this->updateAuxCategoryTask->run($id, $data);
    }
}
