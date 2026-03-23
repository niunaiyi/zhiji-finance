<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\DeactivateAccountAction;
use App\Containers\Finance\Foundation\UI\API\Transformers\AccountTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DeactivateAccountController extends ApiController
{
    public function __construct(
        private readonly DeactivateAccountAction $action
    ) {}

    public function __invoke(int $id): JsonResponse
    {
        $account = $this->action->run($id);
        return $this->json($this->transform($account, AccountTransformer::class));
    }
}
