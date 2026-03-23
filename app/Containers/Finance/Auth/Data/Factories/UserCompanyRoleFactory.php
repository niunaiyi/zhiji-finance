<?php

namespace App\Containers\Finance\Auth\Data\Factories;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Factories\Factory;

class UserCompanyRoleFactory extends Factory
{
    protected $model = UserCompanyRole::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'role' => 'accountant',
            'is_active' => true,
        ];
    }
}
