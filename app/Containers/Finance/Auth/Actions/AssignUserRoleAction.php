<?php

namespace App\Containers\Finance\Auth\Actions;

use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Auth\Tasks\AssignUserRoleTask;
use App\Containers\Finance\Auth\Tasks\ValidateUserCompanyAccessTask;
use App\Ship\Parents\Actions\Action;

class AssignUserRoleAction extends Action
{
    public function __construct(
        private readonly ValidateUserCompanyAccessTask $validateUserCompanyAccessTask,
        private readonly AssignUserRoleTask $assignUserRoleTask,
    ) {}

    public function run(int $targetUserId, int $companyId, string $role): UserCompanyRole
    {
        $currentUserId = auth()->id();
        throw_if(!$currentUserId, new \RuntimeException('User must be authenticated'));

        // Only admins can assign roles
        $this->validateUserCompanyAccessTask->run($currentUserId, $companyId, 'admin');

        return $this->assignUserRoleTask->run($targetUserId, $companyId, $role);
    }
}
