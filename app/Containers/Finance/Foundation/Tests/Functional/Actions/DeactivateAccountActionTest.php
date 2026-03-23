<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\DeactivateAccountAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class DeactivateAccountActionTest extends TestCase
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

    public function testDeactivateAccountSucceedsWithoutChildren(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        // Act
        $action = app(DeactivateAccountAction::class);
        $deactivated = $action->run($account->id);

        // Assert
        $this->assertFalse($deactivated->is_active);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }

    public function testDeactivateAccountFailsIfHasChildren(): void
    {
        // Arrange
        $parent = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'is_active' => true,
        ]);

        $child = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot deactivate account with children');

        $action = app(DeactivateAccountAction::class);
        $action->run($parent->id);
    }

    public function testDeactivateAccountFailsEvenWithInactiveChildren(): void
    {
        // Arrange
        $parent = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'is_active' => true,
        ]);

        $child = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'parent_id' => $parent->id,
            'level' => 2,
            'is_active' => false, // Child is already inactive
        ]);

        // Act & Assert
        // Should still fail because child exists (even if inactive)
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot deactivate account with children');

        $action = app(DeactivateAccountAction::class);
        $action->run($parent->id);
    }

    public function testDeactivateAccountWithMultipleChildren(): void
    {
        // Arrange
        $parent = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'is_active' => true,
        ]);

        // Create multiple children
        Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010002',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot deactivate account with children');

        $action = app(DeactivateAccountAction::class);
        $action->run($parent->id);
    }

    public function testDeactivateAlreadyInactiveAccount(): void
    {
        // Arrange
        $account = Account::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => false,
        ]);

        // Act
        $action = app(DeactivateAccountAction::class);
        $deactivated = $action->run($account->id);

        // Assert - Should succeed (idempotent operation)
        $this->assertFalse($deactivated->is_active);
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'is_active' => false,
        ]);
    }
}
