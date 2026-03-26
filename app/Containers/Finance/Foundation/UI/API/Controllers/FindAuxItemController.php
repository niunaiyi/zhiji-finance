<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\FindAuxItemByIdAction;
use App\Containers\Finance\Foundation\UI\API\Requests\FindAuxItemRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class FindAuxItemController extends ApiController
{
    public function __construct(
        private readonly FindAuxItemByIdAction $action
    ) {}

    public function __invoke(FindAuxItemRequest $request, int $id): JsonResponse
    {
        $auxItem = $this->action->run($id);
        return Response::create($auxItem, AuxItemTransformer::class)->ok();
    }
}
