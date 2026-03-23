<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Seeders\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PeriodSeeder
 *
 * Seeds 12 accounting periods for fiscal year 2026 for the default company.
 * Respects the company's fiscal_year_start setting to handle fiscal years
 * that cross calendar years (e.g., July 2026 - June 2027).
 *
 * Dependencies:
 * - Requires CompanySeeder to run first (needs DEFAULT company)
 *
 * Behavior:
 * - Creates periods 1-12 for fiscal year 2026
 * - Period 1 is set to 'open', all others are 'closed'
 * - Uses firstOrCreate() for idempotency (safe to run multiple times)
 * - Wrapped in transaction for atomicity
 */
class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $company = Company::where('code', 'DEFAULT')->first();

            if (!$company) {
                return;
            }

            $fiscalYear = 2026;
            $startMonth = $company->fiscal_year_start;

            for ($i = 0; $i < 12; $i++) {
                $periodNumber = $i + 1;
                $monthOffset = $startMonth - 1 + $i;
                $year = $fiscalYear + floor($monthOffset / 12);
                $month = ($monthOffset % 12) + 1;

                $startDate = Carbon::create($year, $month, 1);
                $endDate = $startDate->copy()->endOfMonth();

                Period::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'fiscal_year' => $fiscalYear,
                        'period_number' => $periodNumber,
                    ],
                    [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'status' => $periodNumber === 1 ? 'open' : 'closed',
                    ]
                );
            }
        });
    }
}
