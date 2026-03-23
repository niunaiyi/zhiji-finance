<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\AuxCategoryRepository;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tasks\Task;

class UpdateAuxCategoryTask extends Task
{
    public function __construct(
        private readonly AuxCategoryRepository $repository
    ) {}

    public function run(int $id, array $data): AuxCategory
    {
        return $this->repository->update($data, $id);
    }
}
