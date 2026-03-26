<?php

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use Illuminate\Support\Facades\Hash;

$u = User::where('email', 'admin@acc01.com')->first();
if (!$u) {
    $u = new User();
    $u->email = 'admin@acc01.com';
}
$u->name = 'ACC01 Admin';
$u->password = 'password'; // 如果模型有自动 Hash 逻辑，传明文即可；如果 401，说明没自动 Hash
$u->save();

// 强制再次手动 Hash 一遍以确保万无一失 (如果模型里定义了 password 字段，通常 Apiato 会处理)
// 但为了 100% 成功：
$u->password = Hash::make('password');
$u->save();

$c = Company::firstOrCreate(['code' => 'ACC01'], ['name' => '账套1', 'status' => 'active']);
UserCompanyRole::firstOrCreate(['user_id' => $u->id, 'company_id' => $c->id], ['role' => 'admin']);

echo "DONE: Password hashed and user updated.\n";
