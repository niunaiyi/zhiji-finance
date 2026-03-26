<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\FindPeriodByIdAction;
use App\Containers\Finance\Foundation\UI\API\Requests\FindPeriodRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class FindPeriodController extends ApiController
{
    public function __construct(
        private readonly FindPeriodByIdAction $action
    ) {}

    public function __invoke(FindPeriodRequest $request, int $id): JsonResponse
    {
        $period = $this->action->run($id);
        return Response::create($period, PeriodTransformer::class)->ok();
    }
}
