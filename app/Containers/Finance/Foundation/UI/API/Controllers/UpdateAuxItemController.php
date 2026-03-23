<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\UpdateAuxItemAction;
use App\Containers\Finance\Foundation\UI\API\Requests\UpdateAuxItemRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateAuxItemController extends ApiController
{
    public function __construct(
        private readonly UpdateAuxItemAction $action
    ) {}

    public function __invoke(int $id, UpdateAuxItemRequest $request): JsonResponse
    {
        $auxItem = $this->action->run($id, $request->validated());
        return $this->ok($this->transform($auxItem, AuxItemTransformer::class));
    }
}
