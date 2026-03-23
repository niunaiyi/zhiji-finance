<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Tasks\FindPeriodByIdTask;
use App\Containers\Finance\Foundation\UI\API\Requests\FindPeriodRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class FindPeriodController extends ApiController
{
    public function __construct(
        private readonly FindPeriodByIdTask $task
    ) {}

    public function __invoke(FindPeriodRequest $request, int $id): JsonResponse
    {
        $period = $this->task->run($id);
        return $this->ok($this->transform($period, PeriodTransformer::class));
    }
}
