<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use App\Containers\Finance\Auth\Actions\AssignUserRoleAction;
use App\Containers\Finance\Auth\UI\API\Requests\AssignRoleRequest;
use App\Containers\Finance\Auth\UI\API\Transformers\UserCompanyRoleTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class AssignRoleController extends ApiController
{
    public function __construct(
        private readonly AssignUserRoleAction $action
    ) {}

    public function __invoke(AssignRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $userCompanyRole = $this->action->run(
            $validated['user_id'],
            $validated['company_id'],
            $validated['role']
        );

        return $this->json($this->transform($userCompanyRole, UserCompanyRoleTransformer::class));
    }
}
