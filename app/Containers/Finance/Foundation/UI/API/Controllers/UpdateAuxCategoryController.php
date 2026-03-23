<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

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

    public function __invoke(UpdateAuxCategoryRequest $request): JsonResponse
    {
        $auxCategory = $this->action->run(
            $request->id,
            $request->validated()
        );
        return $this->ok($this->transform($auxCategory, AuxCategoryTransformer::class));
    }
}
