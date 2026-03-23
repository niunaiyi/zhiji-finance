<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Data\Repositories\PeriodRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Validation\ValidationException;

class ValidatePeriodOverlapTask extends Task
{
    public function __construct(
        private readonly PeriodRepository $repository
    ) {}

    public function run(int $fiscalYear, string $startDate, string $endDate): void
    {
        // Query for existing periods in the same fiscal year
        $overlappingPeriod = $this->repository
            ->findWhere([
                'fiscal_year' => $fiscalYear,
            ])
            ->filter(function ($period) use ($startDate, $endDate) {
                // Check overlap: new_start <= existing_end AND new_end >= existing_start
                return $startDate <= $period->end_date->format('Y-m-d')
                    && $endDate >= $period->start_date->format('Y-m-d');
            })
            ->first();

        if ($overlappingPeriod) {
            throw ValidationException::withMessages([
                'start_date' => sprintf(
                    'Period overlaps with existing period %d (%s to %s)',
                    $overlappingPeriod->period_number,
                    $overlappingPeriod->start_date->format('Y-m-d'),
                    $overlappingPeriod->end_date->format('Y-m-d')
                ),
            ]);
        }
    }
}
