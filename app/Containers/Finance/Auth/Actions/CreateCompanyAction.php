<?php

namespace App\Containers\Finance\Auth\Actions;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Tasks\AssignUserRoleTask;
use App\Containers\Finance\Auth\Tasks\CreateCompanyTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class CreateCompanyAction extends Action
{
    public function __construct(
        private readonly CreateCompanyTask $createCompanyTask,
        private readonly AssignUserRoleTask $assignUserRoleTask,
    ) {}

    public function run(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            throw_if(!$userId, new \RuntimeException('User must be authenticated'));

            $company = $this->createCompanyTask->run($data);

            $this->assignUserRoleTask->run(
                $userId,
                $company->id,
                'admin'
            );

            return $company;
        });
    }
}
