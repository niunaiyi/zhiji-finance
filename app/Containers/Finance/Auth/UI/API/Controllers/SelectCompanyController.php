<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;

class SelectCompanyController extends ApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
        ]);

        $user = $request->user() ?? auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $companyId = $request->company_id;

        // Verify user has access to this company
        $userRole = UserCompanyRole::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$userRole) {
            return response()->json([
                'message' => 'You do not have access to this company'
            ], 403);
        }

        $company = Company::find($companyId);

        if ($company->status !== 'active') {
            return response()->json([
                'message' => 'This company is not active'
            ], 403);
        }

        // Create token (company context will be sent via X-Company-Id header)
        $token = $user->createToken('auth-token')->accessToken;

        return response()->json([
            'token' => $token,
            'company' => [
                'id' => $company->id,
                'code' => $company->code,
                'name' => $company->name,
            ],
            'role' => $userRole->role,
        ]);
    }
}
