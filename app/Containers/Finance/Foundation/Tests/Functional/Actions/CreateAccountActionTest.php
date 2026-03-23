<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\CreateAccountAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class CreateAccountActionTest extends TestCase
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

    public function testCreateRootAccountWithElementTypeAndBalanceDirection(): void
    {
        // Arrange
        $data = [
            'code' => '1001',
            'name' => 'Cash',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
            'is_active' => true,
        ];

        // Act
        $action = app(CreateAccountAction::class);
        $account = $action->run($data);

        // Assert
        $this->assertDatabaseHas('accounts', [
            'company_id' => $this->company->id,
            'code' => '1001',
            'name' => 'Cash',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
            'parent_id' => null,
        ]);

        $this->assertEquals(1, $account->level);
        $this->assertEquals('asset', $account->element_type);
        $this->assertEquals('debit', $account->balance_direction);
    }

    public function testCreateChildAccountCalculatesLevelFromCode(): void
    {
        // Arrange
        $parent = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $data = [
            'code' => '10010001',
            'name' => 'Cash - Bank A',
            'parent_id' => $parent->id,
        ];

        // Act
        $action = app(CreateAccountAction::class);
        $account = $action->run($data);

        // Assert
        $this->assertEquals(2, $account->level);
        $this->assertEquals($parent->id, $account->parent_id);
    }

    public function testCreateChildAccountInheritsElementTypeAndBalanceDirection(): void
    {
        // Arrange
        $parent = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $data = [
            'code' => '10010001',
            'name' => 'Cash - Bank A',
            'parent_id' => $parent->id,
        ];

        // Act
        $action = app(CreateAccountAction::class);
        $account = $action->run($data);

        // Assert
        $this->assertEquals($parent->element_type, $account->element_type);
        $this->assertEquals($parent->balance_direction, $account->balance_direction);
    }

    public function testCreateThirdLevelAccount(): void
    {
        // Arrange
        $level1 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $level2 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'level' => 2,
            'parent_id' => $level1->id,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $data = [
            'code' => '100100010001',
            'name' => 'Cash - Bank A - Account 1',
            'parent_id' => $level2->id,
        ];

        // Act
        $action = app(CreateAccountAction::class);
        $account = $action->run($data);

        // Assert
        $this->assertEquals(3, $account->level);
        $this->assertEquals($level2->id, $account->parent_id);
        $this->assertEquals('asset', $account->element_type);
        $this->assertEquals('debit', $account->balance_direction);
    }

    public function testCreateFourthLevelAccount(): void
    {
        // Arrange
        $level1 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $level2 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010001',
            'level' => 2,
            'parent_id' => $level1->id,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $level3 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '100100010001',
            'level' => 3,
            'parent_id' => $level2->id,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $data = [
            'code' => '1001000100010001',
            'name' => 'Cash - Bank A - Account 1 - Sub 1',
            'parent_id' => $level3->id,
        ];

        // Act
        $action = app(CreateAccountAction::class);
        $account = $action->run($data);

        // Assert
        $this->assertEquals(4, $account->level);
        $this->assertEquals($level3->id, $account->parent_id);
    }

    public function testValidateCodeFormatRejectsThreeDigits(): void
    {
        // Arrange
        $data = [
            'code' => '123',
            'name' => 'Invalid Account',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Account code must be 4-digit segments');

        $action = app(CreateAccountAction::class);
        $action->run($data);
    }

    public function testValidateCodeFormatRejectsFiveDigits(): void
    {
        // Arrange
        $data = [
            'code' => '12345',
            'name' => 'Invalid Account',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Account code must be 4-digit segments');

        $action = app(CreateAccountAction::class);
        $action->run($data);
    }

    public function testValidateCodeFormatRejectsNonNumeric(): void
    {
        // Arrange
        $data = [
            'code' => 'ABCD',
            'name' => 'Invalid Account',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Account code must be 4-digit segments');

        $action = app(CreateAccountAction::class);
        $action->run($data);
    }

    public function testValidateCodeFormatRejectsSevenDigits(): void
    {
        // Arrange
        $data = [
            'code' => '1234567',
            'name' => 'Invalid Account',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Account code must be 4-digit segments');

        $action = app(CreateAccountAction::class);
        $action->run($data);
    }

    public function testValidateMaxFourLevels(): void
    {
        // Arrange - Try to create a 5-level account (20 digits)
        $data = [
            'code' => '12345678901234567890',
            'name' => 'Too Deep Account',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ];

        // Act & Assert
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Account code cannot exceed 4 levels (16 digits)');

        $action = app(CreateAccountAction::class);
        $action->run($data);
    }

    public function testValidateMaxFourLevelsExactly16Digits(): void
    {
        // Arrange - 16 digits should be valid (4 levels)
        $level1 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'level' => 1,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $level2 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '10010101',
            'level' => 2,
            'parent_id' => $level1->id,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $level3 = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '100101010202',
            'level' => 3,
            'parent_id' => $level2->id,
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $data = [
            'code' => '1001010102020303',
            'name' => 'Max Level Account',
            'parent_id' => $level3->id,
        ];

        // Act
        $action = app(CreateAccountAction::class);
        $account = $action->run($data);

        // Assert
        $this->assertEquals(4, $account->level);
        $this->assertEquals(16, strlen($account->code));
    }
}
