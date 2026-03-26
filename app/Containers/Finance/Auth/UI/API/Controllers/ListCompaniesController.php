<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Auth\Actions\ListUserCompaniesAction;
use App\Containers\Finance\Auth\UI\API\Requests\ListCompaniesRequest;
use App\Containers\Finance\Auth\UI\API\Transformers\CompanyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListCompaniesController extends ApiController
{
    public function __construct(
        private readonly ListUserCompaniesAction $action
    ) {}

    public function __invoke(ListCompaniesRequest $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $companies = \App\Containers\Finance\Auth\Models\Company::whereHas('userRoles', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('is_active', true);
        })->where('status', 'active')->get();

        return response()->json([
            'data' => $companies
        ]);
    }
}
