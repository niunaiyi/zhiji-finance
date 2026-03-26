<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\CreateAuxItemAction;
use App\Containers\Finance\Foundation\UI\API\Requests\CreateAuxItemRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateAuxItemController extends ApiController
{
    public function __construct(
        private readonly CreateAuxItemAction $action
    ) {}

    public function __invoke(CreateAuxItemRequest $request): JsonResponse
    {
        $auxItem = $this->action->run($request->validated());
        return Response::create($auxItem, AuxItemTransformer::class)->created();
    }
}
