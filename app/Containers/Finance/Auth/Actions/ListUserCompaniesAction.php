<?php

namespace App\Containers\Finance\Auth\Actions;

use App\Containers\Finance\Auth\Tasks\FindUserCompaniesTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Collection;

class ListUserCompaniesAction extends Action
{
    public function __construct(
        private readonly FindUserCompaniesTask $findUserCompaniesTask,
    ) {}

    public function run(): Collection
    {
        $userId = auth()->id();
        throw_if(!$userId, new AuthenticationException('User must be authenticated'));

        return $this->findUserCompaniesTask->run($userId);
    }
}
