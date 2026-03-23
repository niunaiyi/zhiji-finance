<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\ListAuxCategoriesAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ListAuxCategoriesRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\AuxCategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListAuxCategoriesController extends ApiController
{
    public function __construct(
        private readonly ListAuxCategoriesAction $action
    ) {}

    public function __invoke(ListAuxCategoriesRequest $request): JsonResponse
    {
        $auxCategories = $this->action->run($request->all());
        return $this->ok($this->transform($auxCategories, AuxCategoryTransformer::class));
    }
}
