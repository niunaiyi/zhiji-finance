<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\DeactivateAccountAction;
use App\Containers\Finance\Foundation\UI\API\Requests\DeactivateAccountRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DeactivateAccountController extends ApiController
{
    public function __construct(
        private readonly DeactivateAccountAction $action
    ) {}

    public function __invoke(DeactivateAccountRequest $request, int $id): JsonResponse
    {
        $account = $this->action->run($id);
        return Response::create($account, AccountTransformer::class)->ok();
    }
}
