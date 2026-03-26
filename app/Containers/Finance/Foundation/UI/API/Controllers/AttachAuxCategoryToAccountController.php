<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\AttachAuxCategoryToAccountAction;
use App\Containers\Finance\Foundation\UI\API\Requests\AttachAuxCategoryToAccountRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class AttachAuxCategoryToAccountController extends ApiController
{
    public function __construct(
        private readonly AttachAuxCategoryToAccountAction $action
    ) {}

    public function __invoke(AttachAuxCategoryToAccountRequest $request): JsonResponse
    {
        $account = $this->action->run($request->validated());

        return Response::create($account, AccountTransformer::class)->ok();
    }
}
