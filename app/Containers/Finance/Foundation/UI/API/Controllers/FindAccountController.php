<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\FindAccountByIdAction;
use App\Containers\Finance\Foundation\UI\API\Requests\FindAccountRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class FindAccountController extends ApiController
{
    public function __construct(
        private readonly FindAccountByIdAction $action
    ) {}

    public function __invoke(FindAccountRequest $request, int $id): JsonResponse
    {
        $account = $this->action->run($id);
        return Response::create($account, AccountTransformer::class)->ok();
    }
}
