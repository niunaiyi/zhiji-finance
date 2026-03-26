<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\UpdateAuxCategoryAction;
use App\Containers\Finance\Foundation\UI\API\Requests\UpdateAuxCategoryRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxCategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class UpdateAuxCategoryController extends ApiController
{
    public function __construct(
        private readonly UpdateAuxCategoryAction $action
    ) {}

    public function __invoke(UpdateAuxCategoryRequest $request, int $id): JsonResponse
    {
        // Action expects (int $id, array $data)
        $auxCategory = $this->action->run($id, $request->validated());
        return Response::create($auxCategory, AuxCategoryTransformer::class)->ok();
    }
}
