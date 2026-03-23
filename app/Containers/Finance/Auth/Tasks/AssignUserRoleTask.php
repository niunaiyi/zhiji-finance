<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tasks\Task;

class AssignUserRoleTask extends Task
{
    public function run(int $userId, int $companyId, string $role): UserCompanyRole
    {
        return UserCompanyRole::updateOrCreate(
            [
                'user_id' => $userId,
                'company_id' => $companyId,
            ],
            [
                'role' => $role,
                'is_active' => true,
            ]
        );
    }
}
