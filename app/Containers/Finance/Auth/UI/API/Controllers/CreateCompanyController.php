<?php

namespace App\Containers\Finance\Auth\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\Finance\Auth\Actions\CreateCompanyAction;
use App\Containers\Finance\Auth\UI\API\Requests\CreateCompanyRequest;
use App\Containers\Finance\Auth\UI\API\Transformers\CompanyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class CreateCompanyController extends ApiController
{
    public function __construct(
        private readonly CreateCompanyAction $action
    ) {}

    public function __invoke(CreateCompanyRequest $request): JsonResponse
    {
        $company = $this->action->run($request->validated());

        return Response::create($company, CompanyTransformer::class)->created();
    }
}
