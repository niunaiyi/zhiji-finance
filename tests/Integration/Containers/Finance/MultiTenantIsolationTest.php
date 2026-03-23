<?php

namespace Tests\Integration\Containers\Finance;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\Period;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;
    public function testUserCannotSeeOtherCompanyData(): void
    {
        // Arrange
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user = User::factory()->create();

        UserCompanyRole::factory()->create([
            'user_id' => $user->id,
            'company_id' => $company1->id,
            'role' => 'admin',
        ]);

        app()->instance('current.company_id', $company1->id);
        $account1 = Account::factory()->create(['company_id' => $company1->id]);

        app()->instance('current.company_id', $company2->id);
        $account2 = Account::factory()->create(['company_id' => $company2->id]);

        // Act
        app()->instance('current.company_id', $company1->id);
        $accounts = Account::all();

        // Assert
        $this->assertCount(1, $accounts);
        $this->assertEquals($account1->id, $accounts->first()->id);
    }

    public function testAccountHierarchyAutoUpdatesParentIsDetail(): void
    {
        $company = Company::factory()->create();
        app()->instance('current.company_id', $company->id);

        $parent = Account::factory()->create(['is_detail' => true]);
        $this->assertTrue($parent->is_detail);

        $child = Account::factory()->create(['parent_id' => $parent->id]);

        $parent->refresh();
        $this->assertFalse($parent->is_detail);
    }

    public function testPeriodStatusTransitionsAreOneWay(): void
    {
        $company = Company::factory()->create();
        app()->instance('current.company_id', $company->id);

        $period = Period::factory()->create(['status' => 'open']);

        $period->update(['status' => 'closed']);
        $this->assertEquals('closed', $period->status);

        $period->update(['status' => 'locked']);
        $this->assertEquals('locked', $period->status);

        // Cannot reopen locked period
        $this->expectException(ValidationException::class);
        $period->update(['status' => 'open']);
    }
}
