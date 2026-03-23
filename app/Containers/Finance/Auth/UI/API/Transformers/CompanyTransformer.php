<?php

namespace App\Containers\Finance\Auth\UI\API\Transformers;

use App\Containers\Finance\Auth\Models\Company;
use App\Ship\Parents\Transformers\Transformer;

class CompanyTransformer extends Transformer
{
    protected array $defaultIncludes = [];

    protected array $availableIncludes = [];

    public function transform(Company $company): array
    {
        return [
            'id' => $company->id,
            'code' => $company->code,
            'name' => $company->name,
            'fiscal_year_start' => $company->fiscal_year_start,
            'status' => $company->status,
            'created_at' => $company->created_at?->toIso8601String(),
        ];
    }
}
