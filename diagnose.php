<?php
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;

$email = 'admin@acc001.com';
$password = 'password';

$u = User::where('email', $email)->first();
if (!$u) {
    echo "USER_NOT_FOUND\n";
    return;
}

echo "EMAIL: " . $u->email . "\n";
echo "HASH: " . $u->password . "\n";
echo "CHECK: " . (Hash::check($password, $u->password) ? "OK" : "FAIL") . "\n";

// Check companies
$companies = Company::whereHas('userRoles', function ($query) use ($u) {
    $query->where('user_id', $u->id)
          ->where('is_active', true);
})->where('status', 'active')->get();

echo "COMPANIES: " . $companies->count() . "\n";
foreach ($companies as $c) {
    echo " - " . $c->code . " (ID: " . $c->id . ")\n";
}

// Manual Test login via Request simulation
$request = request();
$request->merge(['email' => $email, 'password' => $password]);
$controller = app(App\Containers\Finance\Auth\UI\API\Controllers\LoginController::class);
try {
    $response = $controller->__invoke($request);
    echo "LOGIN_API_STATUS: " . $response->getStatusCode() . "\n";
    echo "LOGIN_API_CONTENT: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "LOGIN_API_ERROR: " . $e->getMessage() . "\n";
}
