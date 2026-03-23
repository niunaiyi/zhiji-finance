<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use App\Containers\Finance\Auth\Actions\ListUserCompaniesAction;
use App\Containers\Finance\Auth\UI\API\Requests\ListCompaniesRequest;
use App\Containers\Finance\Auth\UI\API\Transformers\CompanyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class ListCompaniesController extends ApiController
{
    public function __construct(
        private readonly ListUserCompaniesAction $action
    ) {}

    public function __invoke(ListCompaniesRequest $request): JsonResponse
    {
        $companies = $this->action->run();

        return $this->json($this->transform($companies, CompanyTransformer::class));
    }
}
