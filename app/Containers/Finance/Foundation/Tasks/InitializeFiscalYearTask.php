<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Actions\CreatePeriodAction;
use App\Containers\Finance\Foundation\Data\Repositories\PeriodRepository;
use App\Ship\Parents\Tasks\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InitializeFiscalYearTask extends Task
{
    public function __construct(
        private readonly PeriodRepository $periodRepository,
        private readonly CreatePeriodAction $createPeriodAction,
    ) {}

    public function run(array $data): array
    {
        $companyId = $data['company_id'];
        $fiscalYear = $data['fiscal_year'];

        // Get the company to fetch fiscal_year_start
        $company = Company::findOrFail($companyId);
        $fiscalYearStart = $company->fiscal_year_start ?? 1;

        // Check if periods already exist for this fiscal year
        $existingPeriods = $this->periodRepository->findWhere([
            'fiscal_year' => $fiscalYear,
        ]);

        if ($existingPeriods->isNotEmpty()) {
            throw ValidationException::withMessages([
                'fiscal_year' => "Periods already exist for fiscal year {$fiscalYear}",
            ]);
        }

        // Create 12 periods in a transaction
        return DB::transaction(function () use ($fiscalYear, $fiscalYearStart) {
            $periods = [];

            for ($periodNumber = 1; $periodNumber <= 12; $periodNumber++) {
                // Calculate the calendar month (1-12)
                $calendarMonth = (($fiscalYearStart + $periodNumber - 2) % 12) + 1;

                // Calculate the calendar year
                $calendarYear = $fiscalYear;
                if ($fiscalYearStart + $periodNumber - 1 > 12) {
                    $calendarYear = $fiscalYear + 1;
                }

                // Create start and end dates
                $startDate = Carbon::create($calendarYear, $calendarMonth, 1);
                $endDate = $startDate->copy()->endOfMonth();

                // First period is 'open', rest are 'closed'
                $status = $periodNumber === 1 ? 'open' : 'closed';

                // Create the period using CreatePeriodAction
                $period = $this->createPeriodAction->run([
                    'fiscal_year' => $fiscalYear,
                    'period_number' => $periodNumber,
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'status' => $status,
                ]);

                $periods[] = $period;
            }

            return $periods;
        });
    }
}
