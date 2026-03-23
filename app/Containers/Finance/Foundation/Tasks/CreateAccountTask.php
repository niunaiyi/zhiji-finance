<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\AccountRepository;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;

class CreateAccountTask extends Task
{
    public function __construct(
        private readonly AccountRepository $repository
    ) {}

    public function run(array $data): Account
    {
        return $this->repository->create($data);
    }
}
