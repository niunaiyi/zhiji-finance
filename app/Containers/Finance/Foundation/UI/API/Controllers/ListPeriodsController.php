<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\ListPeriodsAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ListPeriodsRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListPeriodsController extends ApiController
{
    public function __construct(
        private readonly ListPeriodsAction $action
    ) {}

    public function __invoke(ListPeriodsRequest $request): JsonResponse
    {
        $periods = $this->action->run($request->validated());
        return $this->ok($this->transform($periods, PeriodTransformer::class));
    }
}
