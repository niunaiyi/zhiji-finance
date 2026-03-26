<?php
use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use Illuminate\Support\Facades\Hash;

// 创建超级管理员
$super = User::where('email', 'admin@admin.com')->first() ?: new User();
$super->name = 'Super Admin';
$super->email = 'admin@admin.com';
$super->password = 'password';
$super->is_super_admin = true;
$super->save();

// 创建账套1
$c1 = Company::where('code', 'ACC001')->first() ?: Company::create(['code' => 'ACC001', 'name' => '账套1', 'status' => 'active']);
$u1 = User::where('email', 'admin@acc001.com')->first() ?: new User();
$u1->name = 'ACC001 Admin';
$u1->email = 'admin@acc001.com';
$u1->password = 'password';
$u1->save();
UserCompanyRole::updateOrCreate(['user_id' => $u1->id, 'company_id' => $c1->id], ['role' => 'admin']);

// 创建账套2
$c2 = Company::where('code', 'ACC002')->first() ?: Company::create(['code' => 'ACC002', 'name' => '账套2', 'status' => 'active']);
$u2 = User::where('email', 'admin@acc002.com')->first() ?: new User();
$u2->name = 'ACC002 Admin';
$u2->email = 'admin@acc002.com';
$u2->password = 'password';
$u2->save();
UserCompanyRole::updateOrCreate(['user_id' => $u2->id, 'company_id' => $c2->id], ['role' => 'admin']);

echo "SEED_SUCCESS\n";
