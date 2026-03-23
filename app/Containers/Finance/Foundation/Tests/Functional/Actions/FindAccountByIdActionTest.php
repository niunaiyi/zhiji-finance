<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\FindAccountByIdAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindAccountByIdActionTest extends TestCase
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

    public function testFindAccountByIdReturnsAccount(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Test Account',
        ]);

        // Act
        $action = app(FindAccountByIdAction::class);
        $found = $action->run($account->id);

        // Assert
        $this->assertEquals($account->id, $found->id);
        $this->assertEquals('Test Account', $found->name);
    }

    public function testFindAccountByIdEagerLoadsRelationships(): void
    {
        // Arrange
        $parent = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
        ]);

        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'level' => 2,
            'parent_id' => $parent->id,
        ]);

        $child = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '100100010001',
            'level' => 3,
            'parent_id' => $account->id,
        ]);

        // Act
        $action = app(FindAccountByIdAction::class);
        $found = $action->run($account->id);

        // Assert
        $this->assertTrue($found->relationLoaded('parent'));
        $this->assertTrue($found->relationLoaded('children'));
        $this->assertEquals($parent->id, $found->parent->id);
        $this->assertCount(1, $found->children);
        $this->assertEquals($child->id, $found->children->first()->id);
    }

    public function testFindAccountByIdThrowsExceptionWhenNotFound(): void
    {
        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $action = app(FindAccountByIdAction::class);
        $action->run(99999);
    }

    public function testFindAccountByIdRespectsMultiTenancy(): void
    {
        // Arrange - Create account in different company
        $otherCompany = Company::factory()->create();
        $otherAccount = Account::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        // Act & Assert - Should not find account from other company
        $this->expectException(ModelNotFoundException::class);

        $action = app(FindAccountByIdAction::class);
        $action->run($otherAccount->id);
    }

    public function testFindAccountByIdWithNoParent(): void
    {
        // Arrange - Root account with no parent
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'parent_id' => null,
        ]);

        // Act
        $action = app(FindAccountByIdAction::class);
        $found = $action->run($account->id);

        // Assert
        $this->assertTrue($found->relationLoaded('parent'));
        $this->assertNull($found->parent);
    }

    public function testFindAccountByIdWithNoChildren(): void
    {
        // Arrange - Leaf account with no children
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
        ]);

        // Act
        $action = app(FindAccountByIdAction::class);
        $found = $action->run($account->id);

        // Assert
        $this->assertTrue($found->relationLoaded('children'));
        $this->assertCount(0, $found->children);
    }
}
