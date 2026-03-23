<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\AttachAuxCategoryToAccountAction;
use App\Containers\Finance\Foundation\UI\API\Requests\AttachAuxCategoryToAccountRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class AttachAuxCategoryToAccountController extends ApiController
{
    public function __construct(
        private readonly AttachAuxCategoryToAccountAction $action
    ) {}

    public function __invoke(AttachAuxCategoryToAccountRequest $request): JsonResponse
    {
        $this->action->run($request->validated());
        return $this->noContent();
    }
}
