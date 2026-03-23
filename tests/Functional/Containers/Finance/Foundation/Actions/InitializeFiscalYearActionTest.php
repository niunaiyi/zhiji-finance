<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\InitializeFiscalYearAction;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class InitializeFiscalYearActionTest extends TestCase
{
    private InitializeFiscalYearAction $action;
    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'fiscal_year_start' => 1, // January
        ]);

        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
        app()->instance('current.company_id', $this->company->id);

        $this->action = app(InitializeFiscalYearAction::class);
    }

    public function testInitializeFiscalYearStartingInJanuary(): void
    {
        $data = [
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
        ];

        $periods = $this->action->run($data);

        $this->assertCount(12, $periods);

        // Verify first period
        $firstPeriod = $periods[0];
        $this->assertEquals(2026, $firstPeriod->fiscal_year);
        $this->assertEquals(1, $firstPeriod->period_number);
        $this->assertEquals('2026-01-01', $firstPeriod->start_date->format('Y-m-d'));
        $this->assertEquals('2026-01-31', $firstPeriod->end_date->format('Y-m-d'));
        $this->assertEquals('open', $firstPeriod->status);

        // Verify second period is closed
        $secondPeriod = $periods[1];
        $this->assertEquals(2, $secondPeriod->period_number);
        $this->assertEquals('2026-02-01', $secondPeriod->start_date->format('Y-m-d'));
        $this->assertEquals('2026-02-28', $secondPeriod->end_date->format('Y-m-d'));
        $this->assertEquals('closed', $secondPeriod->status);

        // Verify last period
        $lastPeriod = $periods[11];
        $this->assertEquals(12, $lastPeriod->period_number);
        $this->assertEquals('2026-12-01', $lastPeriod->start_date->format('Y-m-d'));
        $this->assertEquals('2026-12-31', $lastPeriod->end_date->format('Y-m-d'));
        $this->assertEquals('closed', $lastPeriod->status);

        // Verify all periods are in database
        $this->assertEquals(12, Period::where('company_id', $this->company->id)
            ->where('fiscal_year', 2026)
            ->count());
    }

    public function testInitializeFiscalYearStartingInApril(): void
    {
        $this->company->update(['fiscal_year_start' => 4]); // April

        $data = [
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
        ];

        $periods = $this->action->run($data);

        $this->assertCount(12, $periods);

        // Verify first period (April 2026)
        $firstPeriod = $periods[0];
        $this->assertEquals(2026, $firstPeriod->fiscal_year);
        $this->assertEquals(1, $firstPeriod->period_number);
        $this->assertEquals('2026-04-01', $firstPeriod->start_date->format('Y-m-d'));
        $this->assertEquals('2026-04-30', $firstPeriod->end_date->format('Y-m-d'));
        $this->assertEquals('open', $firstPeriod->status);

        // Verify period 9 (December 2026)
        $period9 = $periods[8];
        $this->assertEquals(9, $period9->period_number);
        $this->assertEquals('2026-12-01', $period9->start_date->format('Y-m-d'));
        $this->assertEquals('2026-12-31', $period9->end_date->format('Y-m-d'));
        $this->assertEquals('closed', $period9->status);

        // Verify last period (March 2027 - crosses calendar year)
        $lastPeriod = $periods[11];
        $this->assertEquals(12, $lastPeriod->period_number);
        $this->assertEquals('2027-03-01', $lastPeriod->start_date->format('Y-m-d'));
        $this->assertEquals('2027-03-31', $lastPeriod->end_date->format('Y-m-d'));
        $this->assertEquals('closed', $lastPeriod->status);
    }

    public function testThrowsExceptionIfPeriodsAlreadyExist(): void
    {
        // Create one period for the fiscal year
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Periods already exist for fiscal year 2026');

        $this->action->run([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
        ]);
    }

    public function testFirstPeriodIsOpenRestAreClosed(): void
    {
        $data = [
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
        ];

        $periods = $this->action->run($data);

        // First period should be open
        $this->assertEquals('open', $periods[0]->status);

        // All other periods should be closed
        for ($i = 1; $i < 12; $i++) {
            $this->assertEquals('closed', $periods[$i]->status, "Period {$periods[$i]->period_number} should be closed");
        }
    }

    public function testMultiTenantIsolation(): void
    {
        // Create another company
        $company2 = Company::factory()->create([
            'fiscal_year_start' => 1,
        ]);

        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $company2->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Initialize fiscal year for first company
        $this->action->run([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
        ]);

        // Should be able to initialize same fiscal year for second company
        app()->instance('current.company_id', $company2->id);

        $periods = $this->action->run([
            'company_id' => $company2->id,
            'fiscal_year' => 2026,
        ]);

        $this->assertCount(12, $periods);
        $this->assertEquals($company2->id, $periods[0]->company_id);

        // Verify both companies have their own periods (use withoutGlobalScopes to query across companies)
        $this->assertEquals(12, Period::withoutGlobalScopes()->where('company_id', $this->company->id)->count());
        $this->assertEquals(12, Period::withoutGlobalScopes()->where('company_id', $company2->id)->count());
    }
}
