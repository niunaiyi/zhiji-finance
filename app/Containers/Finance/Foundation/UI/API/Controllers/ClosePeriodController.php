<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\ClosePeriodAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ClosePeriodRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ClosePeriodController extends ApiController
{
    public function __construct(
        private readonly ClosePeriodAction $action
    ) {}

    public function __invoke(ClosePeriodRequest $request, int $id): JsonResponse
    {
        $period = $this->action->run($id);
        return $this->ok($this->transform($period, PeriodTransformer::class));
    }
}
