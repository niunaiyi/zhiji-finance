<?php

namespace App\Containers\Finance\Auth\Data\Seeders;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Seeders\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['code' => 'DEFAULT'],
            [
                'name' => '默认公司',
                'fiscal_year_start' => 1,
                'status' => 'active',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        UserCompanyRole::firstOrCreate(
            [
                'user_id' => $admin->id,
                'company_id' => $company->id,
            ],
            [
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
