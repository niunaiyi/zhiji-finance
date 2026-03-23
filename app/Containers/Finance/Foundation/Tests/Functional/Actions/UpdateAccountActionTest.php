<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\UpdateAccountAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\TestCase;

class UpdateAccountActionTest extends TestCase
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

    public function testUpdateAccountName(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Original Name',
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, ['name' => 'New Name']);

        // Assert
        $this->assertEquals('New Name', $updated->name);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'New Name',
        ]);
    }

    public function testUpdateAccountIsActive(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, ['is_active' => false]);

        // Assert
        $this->assertFalse($updated->is_active);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function testUpdateAccountNameAndIsActiveTogether(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Original',
            'is_active' => true,
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'name' => 'Updated',
            'is_active' => false,
        ]);

        // Assert
        $this->assertEquals('Updated', $updated->name);
        $this->assertFalse($updated->is_active);
    }

    public function testUpdateAccountIgnoresImmutableFields(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'name' => 'Original Name',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
            'is_detail' => true,
            'has_aux' => false,
            'is_active' => true,
        ]);

        // Act - Try to update immutable fields
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'name' => 'Updated Name',
            'code' => '9999', // Should be ignored
            'level' => 5, // Should be ignored
            'element_type' => 'liability', // Should be ignored
            'balance_direction' => 'credit', // Should be ignored
            'is_detail' => false, // Should be ignored
            'has_aux' => true, // Should be ignored
            'is_active' => false,
        ]);

        // Assert - Only name and is_active should change
        $this->assertEquals('Updated Name', $updated->name);
        $this->assertFalse($updated->is_active);

        // Immutable fields should remain unchanged
        $this->assertEquals('1001', $updated->code);
        $this->assertEquals(1, $updated->level);
        $this->assertEquals('asset', $updated->element_type);
        $this->assertEquals('debit', $updated->balance_direction);
        $this->assertTrue($updated->is_detail);
        $this->assertFalse($updated->has_aux);
    }

    public function testUpdateAccountIgnoresCodeChange(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'code' => '2002',
        ]);

        // Assert - Code should remain unchanged
        $this->assertEquals('1001', $updated->code);
    }

    public function testUpdateAccountIgnoresParentIdChange(): void
    {
        // Arrange
        $parent1 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
        ]);

        $parent2 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '2002',
        ]);

        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'parent_id' => $parent1->id,
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'parent_id' => $parent2->id,
        ]);

        // Assert - parent_id should remain unchanged
        $this->assertEquals($parent1->id, $updated->parent_id);
    }

    public function testUpdateAccountIgnoresLevelChange(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'level' => 1,
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'level' => 3,
        ]);

        // Assert - level should remain unchanged
        $this->assertEquals(1, $updated->level);
    }

    public function testUpdateAccountIgnoresElementTypeChange(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'element_type' => 'asset',
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'element_type' => 'liability',
        ]);

        // Assert - element_type should remain unchanged
        $this->assertEquals('asset', $updated->element_type);
    }

    public function testUpdateAccountIgnoresBalanceDirectionChange(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'balance_direction' => 'debit',
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'balance_direction' => 'credit',
        ]);

        // Assert - balance_direction should remain unchanged
        $this->assertEquals('debit', $updated->balance_direction);
    }

    public function testUpdateAccountIgnoresIsDetailChange(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'is_detail' => true,
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'is_detail' => false,
        ]);

        // Assert - is_detail should remain unchanged
        $this->assertTrue($updated->is_detail);
    }

    public function testUpdateAccountIgnoresHasAuxChange(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'has_aux' => false,
        ]);

        // Act
        $action = app(UpdateAccountAction::class);
        $updated = $action->run($account->id, [
            'has_aux' => true,
        ]);

        // Assert - has_aux should remain unchanged
        $this->assertFalse($updated->has_aux);
    }
}
