<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\ListPeriodsAction;
use App\Containers\Finance\Foundation\UI\API\Requests\ListPeriodsRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

/**
 * 获取会计期间列表控制器。
 * 返回系统定义的会计年度及月份区间，用于单据录入和期末结账。
 */
class ListPeriodsController extends ApiController
{
    public function __construct(
        private readonly ListPeriodsAction $action
    ) {}

    public function __invoke(ListPeriodsRequest $request): JsonResponse
    {
        $periods = $this->action->run($request->validated());
        return Response::create($periods, PeriodTransformer::class)->ok();
    }
}
