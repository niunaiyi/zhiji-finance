<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\ListAuxItemsAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ListAuxItemsRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListAuxItemsController extends ApiController
{
    public function __construct(
        private readonly ListAuxItemsAction $action
    ) {}

    public function __invoke(ListAuxItemsRequest $request): JsonResponse
    {
        $auxItems = $this->action->run($request->validated());
        return $this->ok($this->transform($auxItems, AuxItemTransformer::class));
    }
}
