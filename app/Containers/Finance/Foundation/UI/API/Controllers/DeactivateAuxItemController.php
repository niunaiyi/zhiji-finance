<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\DeactivateAuxItemAction;
use App\Containers\Finance\Foundation\UI\API\Requests\DeactivateAuxItemRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class DeactivateAuxItemController extends ApiController
{
    public function __construct(
        private readonly DeactivateAuxItemAction $action
    ) {}

    public function __invoke(DeactivateAuxItemRequest $request, int $id): JsonResponse
    {
        $auxItem = $this->action->run($id);
        return Response::create($auxItem, AuxItemTransformer::class)->ok();
    }
}
