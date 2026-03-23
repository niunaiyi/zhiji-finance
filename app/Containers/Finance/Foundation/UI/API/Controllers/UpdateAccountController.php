<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\UpdateAccountAction;
use App\Containers\Finance\Foundation\UI\API\Requests\UpdateAccountRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateAccountController extends ApiController
{
    public function __construct(
        private readonly UpdateAccountAction $action
    ) {}

    public function __invoke(int $id, UpdateAccountRequest $request): JsonResponse
    {
        $account = $this->action->run($id, $request->validated());
        return $this->json($this->transform($account, AccountTransformer::class));
    }
}
