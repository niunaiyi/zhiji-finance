<?php

namespace App\Containers\Finance\Foundation\UI\API\Transformers;

use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Transformers\Transformer;

class PeriodTransformer extends Transformer
{
    protected array $defaultIncludes = [];
    protected array $availableIncludes = ['company', 'closedBy'];

    public function transform(Period $period): array
    {
        return [
            'id' => $period->id,
            'fiscal_year' => $period->fiscal_year,
            'period_number' => $period->period_number,
            'start_date' => $period->start_date?->toDateString(),
            'end_date' => $period->end_date?->toDateString(),
            'status' => $period->status,
            'closed_at' => $period->closed_at?->toIso8601String(),
            'closed_by' => $period->closed_by,
            'created_at' => $period->created_at?->toIso8601String(),
        ];
    }

    public function includeCompany(Period $period): ?\League\Fractal\Resource\Item
    {
        return $period->company ? $this->item($period->company, new \App\Containers\Finance\Auth\UI\API\Transformers\CompanyTransformer()) : null;
    }

    public function includeClosedBy(Period $period): ?\League\Fractal\Resource\Item
    {
        return $period->closedBy ? $this->item($period->closedBy, new \App\Containers\AppSection\User\UI\API\Transformers\UserTransformer()) : null;
    }
}
