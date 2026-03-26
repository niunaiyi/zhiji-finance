<?php

namespace Database\Seeders;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinalTestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. 超级管理员
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Super Admin', 'password' => 'password', 'is_super_admin' => true]
        );

        // 2. 账套1
        $c1 = Company::updateOrCreate(['code' => 'ACC01'], ['name' => '账套1', 'status' => 'active']);
        $u1 = User::updateOrCreate(
            ['email' => 'admin@acc01.com'],
            ['name' => 'ACC01 Admin', 'password' => 'password']
        );
        UserCompanyRole::updateOrCreate(['user_id' => $u1->id, 'company_id' => $c1->id], ['role' => 'admin']);

        // 3. 账套2
        $c2 = Company::updateOrCreate(['code' => 'ACC02'], ['name' => '账套2', 'status' => 'active']);
        $u2 = User::updateOrCreate(
            ['email' => 'admin@acc02.com'],
            ['name' => 'ACC02 Admin', 'password' => 'password']
        );
        UserCompanyRole::updateOrCreate(['user_id' => $u2->id, 'company_id' => $c2->id], ['role' => 'admin']);
        
        echo "Final Seeding Done.\n";
    }
}
