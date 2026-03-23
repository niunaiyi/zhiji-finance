<?php

namespace App\Containers\Finance\Foundation\UI\API\Controllers;

use App\Containers\Finance\Foundation\Actions\InitializeFiscalYearAction;
use App\Containers\Finance\Foundation\UI\API\Requests\InitializeFiscalYearRequest;
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
        return $this->created(['periods' => $periods, 'message' => 'Fiscal year initialized successfully']);
    }
}
