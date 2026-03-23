<?php

namespace App\Containers\Finance\Auth\Tasks;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class FindUserCompaniesTask extends Task
{
    public function run(int $userId): Collection
    {
        return Company::query()
            ->join('user_company_roles', 'companies.id', '=', 'user_company_roles.company_id')
            ->where('user_company_roles.user_id', $userId)
            ->where('user_company_roles.is_active', true)
            ->where('companies.status', 'active')
            ->select('companies.*', 'user_company_roles.role')
            ->get();
    }
}
