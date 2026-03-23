<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\ListAccountsAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAccountsActionTest extends TestCase
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

    public function testListAccountsReturnsPaginatedResults(): void
    {
        // Arrange
        Account::factory()->count(5)->create([
            'company_id' => $this->company->id,
        ]);

        // Act
        $action = app(ListAccountsAction::class);
        $result = $action->run();

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(5, $result);
    }

    public function testListAccountsRespectsMultiTenancy(): void
    {
        // Arrange - Create accounts for current company
        Account::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        // Create accounts for different company (should not appear)
        $otherCompany = Company::factory()->create();
        Account::factory()->count(2)->create([
            'company_id' => $otherCompany->id,
        ]);

        // Act
        $action = app(ListAccountsAction::class);
        $result = $action->run();

        // Assert
        $this->assertCount(3, $result);
        foreach ($result as $account) {
            $this->assertEquals($this->company->id, $account->company_id);
        }
    }

    public function testListAccountsReturnsEmptyWhenNoAccounts(): void
    {
        // Act
        $action = app(ListAccountsAction::class);
        $result = $action->run();

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(0, $result);
    }

    public function testListAccountsWithFilters(): void
    {
        // Arrange
        Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'name' => 'Cash',
            'element_type' => 'asset',
            'is_active' => true,
        ]);

        Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '2001',
            'name' => 'Accounts Payable',
            'element_type' => 'liability',
            'is_active' => true,
        ]);

        Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1002',
            'name' => 'Bank',
            'element_type' => 'asset',
            'is_active' => false,
        ]);

        // Act - Filter by element_type
        $action = app(ListAccountsAction::class);
        $result = $action->run(['element_type' => 'asset']);

        // Assert
        $this->assertCount(2, $result);
        foreach ($result as $account) {
            $this->assertEquals('asset', $account->element_type);
        }
    }
}
