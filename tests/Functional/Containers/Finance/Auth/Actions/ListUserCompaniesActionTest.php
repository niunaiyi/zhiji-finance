<?php

namespace Tests\Functional\Containers\Finance\Auth\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Actions\ListUserCompaniesAction;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Auth\AuthenticationException;

class ListUserCompaniesActionTest extends TestCase
{
    public function testListUserCompaniesReturnsOnlyUserCompanies(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $company1 = Company::factory()->create(['name' => 'Company 1']);
        $company2 = Company::factory()->create(['name' => 'Company 2']);
        $company3 = Company::factory()->create(['name' => 'Company 3']);

        // User has access to company1 and company2, but not company3
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company1->id,
            'role' => 'admin',
        ]);
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company2->id,
            'role' => 'accountant',
        ]);

        // Act
        $action = app(ListUserCompaniesAction::class);
        $companies = $action->run();

        // Assert
        $this->assertCount(2, $companies);
        $this->assertTrue($companies->contains('id', $company1->id));
        $this->assertTrue($companies->contains('id', $company2->id));
        $this->assertFalse($companies->contains('id', $company3->id));
    }

    public function testListUserCompaniesIncludesRoleInformation(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $company = Company::factory()->create();
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        // Act
        $action = app(ListUserCompaniesAction::class);
        $companies = $action->run();

        // Assert
        $this->assertCount(1, $companies);
        $firstCompany = $companies->first();
        $this->assertNotNull($firstCompany->role);
        $this->assertEquals('admin', $firstCompany->role);
    }

    public function testUnauthenticatedUserFails(): void
    {
        // Arrange - No user authenticated

        // Act & Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('User must be authenticated');

        $action = app(ListUserCompaniesAction::class);
        $action->run();
    }

    public function testOnlyActiveRolesAreReturned(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $company1 = Company::factory()->create(['name' => 'Active Company']);
        $company2 = Company::factory()->create(['name' => 'Inactive Company']);

        // Active role
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company1->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Inactive role
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company2->id,
            'role' => 'accountant',
            'is_active' => false,
        ]);

        // Act
        $action = app(ListUserCompaniesAction::class);
        $companies = $action->run();

        // Assert
        $this->assertCount(1, $companies);
        $this->assertTrue($companies->contains('id', $company1->id));
        $this->assertFalse($companies->contains('id', $company2->id));
    }

    public function testSuspendedCompaniesAreExcluded(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        $activeCompany = Company::factory()->create([
            'name' => 'Active Company',
            'status' => 'active',
        ]);
        $suspendedCompany = Company::factory()->create([
            'name' => 'Suspended Company',
            'status' => 'suspended',
        ]);

        // User has active roles for both companies
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $activeCompany->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $suspendedCompany->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Act
        $action = app(ListUserCompaniesAction::class);
        $companies = $action->run();

        // Assert
        $this->assertCount(1, $companies);
        $this->assertTrue($companies->contains('id', $activeCompany->id));
        $this->assertFalse($companies->contains('id', $suspendedCompany->id));
    }
}
