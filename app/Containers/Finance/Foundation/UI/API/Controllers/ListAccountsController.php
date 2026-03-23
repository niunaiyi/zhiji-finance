<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\ListAccountsAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ListAccountsRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListAccountsController extends ApiController
{
    public function __construct(
        private readonly ListAccountsAction $action
    ) {}

    public function __invoke(ListAccountsRequest $request): JsonResponse
    {
        $accounts = $this->action->run($request->validated());
        return $this->json($this->transform($accounts, AccountTransformer::class));
    }
}
