<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tasks\Task;

class AssignUserRoleTask extends Task
{
    /**
     * Assign or update a user's role for a company.
     *
     * Note: Uses updateOrCreate which will update the role and is_active status
     * if a record already exists for this user-company combination.
     * This is intentional for CreateCompanyAction but be aware of this behavior
     * when reusing this Task in other contexts.
     */
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
