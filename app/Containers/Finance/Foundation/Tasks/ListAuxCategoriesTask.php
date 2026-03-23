<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\AuxCategoryRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAuxCategoriesTask extends Task
{
    public function __construct(
        private readonly AuxCategoryRepository $repository
    ) {}

    public function run(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate();
    }
}
