<?php

namespace Tests\Unit\Containers\Finance\Auth\Models;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Database\QueryException;

class UserCompanyRoleTest extends ShipTestCase
{
    public function testUserCompanyRoleCanBeCreated(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $role = UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);
    }

    public function testUserRelationship(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $role = UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);

        $this->assertInstanceOf(User::class, $role->user);
        $this->assertEquals($user->id, $role->user->id);
    }

    public function testCompanyRelationship(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $role = UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
        ]);

        $this->assertInstanceOf(Company::class, $role->company);
        $this->assertEquals($company->id, $role->company->id);
    }

    public function testUniqueConstraintOnUserAndCompany(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $this->expectException(QueryException::class);

        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'accountant',
        ]);
    }

    public function testIsActiveDefaultsToTrue(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $role = UserCompanyRole::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'accountant',
        ]);

        $this->assertTrue($role->is_active);
    }

    public function testCanCreateMultipleRolesForDifferentCompanies(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $role1 = UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company1->id,
            'role' => 'admin',
        ]);

        $role2 = UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company2->id,
            'role' => 'accountant',
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user->id,
            'company_id' => $company1->id,
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user->id,
            'company_id' => $company2->id,
        ]);
    }

    public function testCanCreateMultipleUsersForSameCompany(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $company = Company::factory()->create();

        $role1 = UserCompanyRole::factory()->create([
            'user_id' => $user1->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        $role2 = UserCompanyRole::factory()->create([
            'user_id' => $user2->id,
            'company_id' => $company->id,
            'role' => 'accountant',
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user1->id,
            'company_id' => $company->id,
        ]);

        $this->assertDatabaseHas('user_company_roles', [
            'user_id' => $user2->id,
            'company_id' => $company->id,
        ]);
    }

    public function testAllRoleTypesCanBeCreated(): void
    {
        $user = User::factory()->create();
        $roles = ['admin', 'accountant', 'auditor', 'viewer'];

        foreach ($roles as $roleType) {
            $company = Company::factory()->create();
            $role = UserCompanyRole::factory()->create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'role' => $roleType,
            ]);

            $this->assertEquals($roleType, $role->role);
        }
    }
}
