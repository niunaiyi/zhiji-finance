<?php

namespace Tests\Unit\Containers\Finance\Foundation\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class PeriodTest extends TestCase
{
    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create([
            'fiscal_year_start' => 1,
        ]);

        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
        app()->instance('current.company_id', $this->company->id);
    }

    public function testCanUpdateNonStatusFieldsOnOpenPeriod(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'open',
        ]);

        // Update start_date without changing status - should succeed
        $period->start_date = '2026-01-02';
        $period->save();

        $this->assertEquals('2026-01-02', $period->fresh()->start_date->format('Y-m-d'));
        $this->assertEquals('open', $period->fresh()->status);
    }

    public function testCanUpdateNonStatusFieldsOnClosedPeriod(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'status' => 'closed',
        ]);

        // Update end_date without changing status - should succeed
        $period->end_date = '2026-01-30';
        $period->save();

        $this->assertEquals('2026-01-30', $period->fresh()->end_date->format('Y-m-d'));
        $this->assertEquals('closed', $period->fresh()->status);
    }

    public function testCanTransitionFromOpenToClosed(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'open',
        ]);

        $period->status = 'closed';
        $period->save();

        $this->assertEquals('closed', $period->fresh()->status);
        $this->assertNotNull($period->fresh()->closed_at);
        $this->assertEquals($this->user->id, $period->fresh()->closed_by);
    }

    public function testCanTransitionFromClosedToOpen(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $this->user->id,
        ]);

        $period->status = 'open';
        $period->save();

        $this->assertEquals('open', $period->fresh()->status);
        $this->assertNull($period->fresh()->closed_at);
        $this->assertNull($period->fresh()->closed_by);
    }

    public function testCanTransitionFromClosedToLocked(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'closed',
        ]);

        $period->status = 'locked';
        $period->save();

        $this->assertEquals('locked', $period->fresh()->status);
    }

    public function testCannotTransitionFromOpenToLocked(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'open',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid status transition from open to locked');

        $period->status = 'locked';
        $period->save();
    }

    public function testCannotTransitionFromLockedToAnyStatus(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'locked',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Locked periods cannot be modified');

        $period->status = 'open';
        $period->save();
    }

    public function testCannotModifyAnyFieldOnLockedPeriod(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'start_date' => '2026-01-01',
            'status' => 'locked',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Locked periods cannot be modified');

        $period->start_date = '2026-01-02';
        $period->save();
    }

    public function testSettingStatusToSameValueIsNoOp(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'closed',
        ]);

        // Setting status to the same value should not trigger validation
        // because Eloquent won't mark it as dirty
        $period->status = 'closed';
        $period->save();

        $this->assertEquals('closed', $period->fresh()->status);
    }
}
