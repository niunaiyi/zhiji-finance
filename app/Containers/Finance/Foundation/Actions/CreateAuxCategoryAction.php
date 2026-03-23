<?php

namespace App\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Tasks\CreateAuxCategoryTask;
use App\Ship\Parents\Actions\Action;

class CreateAuxCategoryAction extends Action
{
    public function __construct(
        private readonly CreateAuxCategoryTask $createAuxCategoryTask,
    ) {}

    public function run(array $data): AuxCategory
    {
        return $this->createAuxCategoryTask->run($data);
    }
}
