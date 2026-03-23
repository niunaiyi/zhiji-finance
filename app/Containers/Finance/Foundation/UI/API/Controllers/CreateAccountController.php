<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\CreateAccountAction;
use App\Containers\Finance\Foundation\UI\API\Requests\CreateAccountRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateAccountController extends ApiController
{
    public function __construct(
        private readonly CreateAccountAction $action
    ) {}

    public function __invoke(CreateAccountRequest $request): JsonResponse
    {
        $account = $this->action->run($request->validated());
        return $this->created($this->transform($account, AccountTransformer::class));
    }
}
