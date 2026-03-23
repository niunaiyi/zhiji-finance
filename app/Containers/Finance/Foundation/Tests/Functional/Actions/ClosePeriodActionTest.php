<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\ClosePeriodAction;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class ClosePeriodActionTest extends TestCase
{
    private ClosePeriodAction $action;
    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();

        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
        app()->instance('current.company_id', $this->company->id);

        $this->action = app(ClosePeriodAction::class);
    }

    public function testSuccessfullyClosesAnOpenPeriod(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'open',
        ]);

        $result = $this->action->run($period->id);

        $this->assertEquals('closed', $result->status);
        $this->assertEquals($period->id, $result->id);

        // Verify in database
        $this->assertDatabaseHas('periods', [
            'id' => $period->id,
            'status' => 'closed',
        ]);
    }

    public function testThrowsExceptionWhenTryingToCloseLockedPeriod(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'locked',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Locked periods cannot be modified');

        $this->action->run($period->id);
    }

    public function testThrowsExceptionWhenTryingToCloseAlreadyClosedPeriod(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $this->user->id,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid status transition from closed to closed');

        $this->action->run($period->id);
    }

    public function testAutomaticallySetsClosedAtAndClosedBy(): void
    {
        $period = Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        $result = $this->action->run($period->id);

        $this->assertNotNull($result->closed_at);
        $this->assertEquals($this->user->id, $result->closed_by);
        $this->assertEqualsWithDelta(now()->timestamp, $result->closed_at->timestamp, 2);
    }

    public function testMultiTenantIsolation(): void
    {
        // Create another company
        $company2 = Company::factory()->create();

        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $company2->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create period for company 2
        $period2 = Period::factory()->create([
            'company_id' => $company2->id,
            'fiscal_year' => 2026,
            'period_number' => 1,
            'status' => 'open',
        ]);

        // Try to close period from company 2 while current company is company 1
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->run($period2->id);
    }
}
