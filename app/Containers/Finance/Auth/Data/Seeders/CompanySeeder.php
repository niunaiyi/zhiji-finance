<?php

namespace App\Containers\Finance\Auth\Data\Seeders;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Seeders\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a default company with admin user for development and testing.
 *
 * WARNING: Uses hardcoded credentials (admin@example.com / password).
 * Do not run in production environments.
 */
class CompanySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
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
        });
    }
}
