<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\AuxItemRepository;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tasks\Task;

class CreateAuxItemTask extends Task
{
    public function __construct(
        private readonly AuxItemRepository $repository
    ) {}

    public function run(array $data): AuxItem
    {
        return $this->repository->create($data);
    }
}
