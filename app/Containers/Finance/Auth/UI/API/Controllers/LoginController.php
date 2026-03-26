<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;

class LoginController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Get companies the user has access to
        $companies = Company::whereHas('userRoles', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('is_active', true);
        })->where('status', 'active')->get();

        // Issue a temp token for company selection
        $tempToken = $user->createToken('temp-token')->accessToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_super_admin' => $user->isSuperAdmin(),
            ],
            'token' => $tempToken,
            'companies' => $companies,
        ]);
    }
}
