<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Foundation\Actions\InitializeFiscalYearAction;
use App\Containers\Finance\Foundation\UI\API\Requests\InitializeFiscalYearRequest;
use App\Containers\Finance\Foundation\UI\API\Transformers\PeriodTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class InitializeFiscalYearController extends ApiController
{
    public function __construct(
        private readonly InitializeFiscalYearAction $action
    ) {}

    public function __invoke(InitializeFiscalYearRequest $request): JsonResponse
    {
        $periods = $this->action->run($request->validated());
        return Response::create($periods, PeriodTransformer::class)->created();
    }
}
