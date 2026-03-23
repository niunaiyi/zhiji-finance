<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\DetachAuxCategoryFromAccountAction;
use App\Containers\Finance\Foundation\UI\API\Requests\DetachAuxCategoryFromAccountRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DetachAuxCategoryFromAccountController extends ApiController
{
    public function __construct(
        private readonly DetachAuxCategoryFromAccountAction $action
    ) {}

    public function __invoke(DetachAuxCategoryFromAccountRequest $request): JsonResponse
    {
        $this->action->run($request->validated());
        return $this->noContent();
    }
}
