<?php

namespace Tests\Functional\Containers\Finance\Auth\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Actions\AssignUserRoleAction;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Auth\Access\AuthorizationException;

class AssignUserRoleActionTest extends TestCase
{
    public function testNonAdminCannotAssignRoles(): void
    {
        // Arrange
        $currentUser = User::factory()->create();
        $targetUser = User::factory()->create();
        $company = Company::factory()->create();

        // Current user is accountant (not admin)
        UserCompanyRole::factory()->create([
            'user_id' => $currentUser->id,
            'company_id' => $company->id,
            'role' => 'accountant',
        ]);

        $this->actingAs($currentUser);

        // Expect exception
        $this->expectException(AuthorizationException::class);

        // Act
        $action = app(AssignUserRoleAction::class);
        $action->run($targetUser->id, $company->id, 'viewer');
    }

    public function testAdminCanAssignRoles(): void
    {
        // Arrange
        $admin = User::factory()->create();
        $targetUser = User::factory()->create();
        $company = Company::factory()->create();

        // Current user is admin
        UserCompanyRole::factory()->create([
            'user_id' => $admin->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        // Act
        $action = app(AssignUserRoleAction::class);
        $result = $action->run($targetUser->id, $company->id, 'accountant');

        // Assert
        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $targetUser->id,
            'company_id' => $company->id,
            'role' => 'accountant',
            'is_active' => true,
        ]);
    }

    public function testUnauthenticatedUserCannotAssignRoles(): void
    {
        // Arrange
        $targetUser = User::factory()->create();
        $company = Company::factory()->create();

        // No authenticated user

        // Expect exception
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('User must be authenticated');

        // Act
        $action = app(AssignUserRoleAction::class);
        $action->run($targetUser->id, $company->id, 'viewer');
    }
}
