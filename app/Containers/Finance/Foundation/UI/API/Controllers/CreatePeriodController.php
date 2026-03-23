<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\CreatePeriodAction;
use App\Containers\Finance\Foundation\UI\API\Requests\CreatePeriodRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreatePeriodController extends ApiController
{
    public function __construct(
        private readonly CreatePeriodAction $action
    ) {}

    public function __invoke(CreatePeriodRequest $request): JsonResponse
    {
        $period = $this->action->run($request->validated());
        return $this->created($this->transform($period, PeriodTransformer::class));
    }
}
