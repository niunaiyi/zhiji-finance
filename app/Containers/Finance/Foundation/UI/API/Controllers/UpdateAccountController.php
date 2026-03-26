<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
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

    public function __invoke(UpdateAccountRequest $request, int $id): JsonResponse
    {
        // Action expects (int $id, array $data)
        $account = $this->action->run($id, $request->validated());
        return Response::create($account, AccountTransformer::class)->ok();
    }
}
