<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\CreatePeriodAction;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class CreatePeriodActionTest extends TestCase
{
    private User $user;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();

        // Assign user to company
        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);

        // Bind company_id to service container for CompanyScope
        app()->instance('current.company_id', $this->company->id);
    }

    public function testSuccessfullyCreatesPeriodWithNoOverlap(): void
    {
        // Arrange
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'status' => 'open',
        ];

        // Act
        $action = app(CreatePeriodAction::class);
        $period = $action->run($data);

        // Assert
        $this->assertInstanceOf(Period::class, $period);
        $this->assertDatabaseHas('periods', [
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'status' => 'open',
        ]);
    }

    public function testThrowsValidationExceptionWhenPeriodOverlaps(): void
    {
        // Arrange - Create existing period
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Try to create overlapping period
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 2,
            'start_date' => '2024-01-15',  // Overlaps with existing period
            'end_date' => '2024-02-15',
            'status' => 'open',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('overlaps');

        $action = app(CreatePeriodAction::class);
        $action->run($data);
    }

    public function testAllowsPeriodsInDifferentFiscalYears(): void
    {
        // Arrange - Create period in 2024
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Create period in 2025 with same dates (different fiscal year)
        $data = [
            'fiscal_year' => 2025,
            'period_number' => 1,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'status' => 'open',
        ];

        // Act
        $action = app(CreatePeriodAction::class);
        $period = $action->run($data);

        // Assert
        $this->assertInstanceOf(Period::class, $period);
        $this->assertEquals(2025, $period->fiscal_year);
    }

    public function testAllowsPeriodsForDifferentCompanies(): void
    {
        // Arrange - Create period for company 1
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Switch to different company
        $company2 = Company::factory()->create();
        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $company2->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
        app()->instance('current.company_id', $company2->id);

        // Create period with same dates for company 2
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'status' => 'open',
        ];

        // Act
        $action = app(CreatePeriodAction::class);
        $period = $action->run($data);

        // Assert
        $this->assertInstanceOf(Period::class, $period);
        $this->assertEquals($company2->id, $period->company_id);
    }

    public function testValidatesUniqueConstraint(): void
    {
        // Arrange - Create existing period
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Try to create duplicate (same company, fiscal_year, period_number)
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-02-01',  // Different dates but same period_number
            'end_date' => '2024-02-28',
            'status' => 'open',
        ];

        // Act & Assert
        $this->expectException(\Apiato\Core\Repositories\Exceptions\ResourceCreationFailed::class);

        $action = app(CreatePeriodAction::class);
        $action->run($data);
    }

    public function testDetectsOverlapAtStartBoundary(): void
    {
        // Arrange - Existing period: Jan 1-31
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // New period starts before and ends on first day of existing
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 2,
            'start_date' => '2023-12-15',
            'end_date' => '2024-01-01',  // Overlaps on boundary
            'status' => 'open',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);

        $action = app(CreatePeriodAction::class);
        $action->run($data);
    }

    public function testDetectsOverlapAtEndBoundary(): void
    {
        // Arrange - Existing period: Jan 1-31
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // New period starts on last day of existing
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 2,
            'start_date' => '2024-01-31',  // Overlaps on boundary
            'end_date' => '2024-02-28',
            'status' => 'open',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);

        $action = app(CreatePeriodAction::class);
        $action->run($data);
    }

    public function testAllowsAdjacentPeriodsWithNoOverlap(): void
    {
        // Arrange - Existing period: Jan 1-31
        Period::factory()->create([
            'company_id' => $this->company->id,
            'fiscal_year' => 2024,
            'period_number' => 1,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // New period starts day after existing ends
        $data = [
            'fiscal_year' => 2024,
            'period_number' => 2,
            'start_date' => '2024-02-01',  // No overlap
            'end_date' => '2024-02-29',
            'status' => 'open',
        ];

        // Act
        $action = app(CreatePeriodAction::class);
        $period = $action->run($data);

        // Assert
        $this->assertInstanceOf(Period::class, $period);
        $this->assertEquals(2, $period->period_number);
    }
}
