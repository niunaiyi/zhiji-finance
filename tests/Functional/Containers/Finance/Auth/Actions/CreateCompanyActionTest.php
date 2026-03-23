<?php

namespace Tests\Functional\Containers\Finance\Auth\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Actions\CreateCompanyAction;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class CreateCompanyActionTest extends TestCase
{
    public function testCreateCompanyAssignsUserAsAdmin(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'code' => 'TEST01',
            'name' => 'Test Company',
            'fiscal_year_start' => 1,
        ];

        // Act
        $action = app(CreateCompanyAction::class);
        $company = $action->run($data);

        // Assert
        $this->assertDatabaseHas('companies', [
            'code' => 'TEST01',
            'name' => 'Test Company',
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function testTransactionRollbackOnFailure(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'code' => 'TEST02',
            'name' => 'Test Company 2',
            'fiscal_year_start' => 1,
        ];

        // Mock AssignUserRoleTask to throw exception
        $mockTask = $this->mock(\App\Containers\Finance\Auth\Tasks\AssignUserRoleTask::class);
        $mockTask->shouldReceive('run')
            ->once()
            ->andThrow(new \RuntimeException('Simulated failure'));

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Simulated failure');

        $action = app(CreateCompanyAction::class);
        $action->run($data);

        // Verify company was NOT created due to rollback
        $this->assertDatabaseMissing('companies', [
            'code' => 'TEST02',
        ]);
    }

    public function testDuplicateCompanyCodeFails(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'code' => 'DUPLICATE',
            'name' => 'First Company',
            'fiscal_year_start' => 1,
        ];

        // Create first company
        $action = app(CreateCompanyAction::class);
        $action->run($data);

        // Act & Assert - Try to create duplicate
        // Repository wraps database constraint violations in ResourceCreationFailed
        $this->expectException(\Apiato\Core\Repositories\Exceptions\ResourceCreationFailed::class);
        $this->expectExceptionMessage('Company creation failed');

        $duplicateData = [
            'code' => 'DUPLICATE',
            'name' => 'Second Company',
            'fiscal_year_start' => 1,
        ];

        $action->run($duplicateData);
    }

    public function testUnauthenticatedUserFails(): void
    {
        // Arrange - No user authenticated
        $data = [
            'code' => 'TEST03',
            'name' => 'Test Company 3',
            'fiscal_year_start' => 1,
        ];

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User must be authenticated');

        $action = app(CreateCompanyAction::class);
        $action->run($data);

        // Verify company was NOT created
        $this->assertDatabaseMissing('companies', [
            'code' => 'TEST03',
        ]);
    }
}
