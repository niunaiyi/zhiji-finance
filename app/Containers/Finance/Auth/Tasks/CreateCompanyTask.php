<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Data\Repositories\CompanyRepository;
use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Tasks\Task;

class CreateCompanyTask extends Task
{
    public function __construct(
        private readonly CompanyRepository $repository
    ) {}

    public function run(array $data): Company
    {
        return $this->repository->create($data);
    }
}
