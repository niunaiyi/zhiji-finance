<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\CreateAuxCategoryAction;
use App\Containers\Finance\Foundation\UI\API\Requests\CreateAuxCategoryRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxCategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateAuxCategoryController extends ApiController
{
    public function __construct(
        private readonly CreateAuxCategoryAction $action
    ) {}

    public function __invoke(CreateAuxCategoryRequest $request): JsonResponse
    {
        $auxCategory = $this->action->run($request->validated());
        return Response::create($auxCategory, AuxCategoryTransformer::class)->created();
    }
}
