<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Auth\Access\AuthorizationException;

class ValidateUserCompanyAccessTask extends Task
{
    /**
     * Validate that the user has the specified role for the company.
     *
     * @throws AuthorizationException
     */
    public function run(int $userId, int $companyId, string $requiredRole): void
    {
        $userRole = UserCompanyRole::query()
            ->where('user_id', $userId)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$userRole || $userRole->role !== $requiredRole) {
            throw new AuthorizationException('User does not have required role for this company');
        }
    }
}
