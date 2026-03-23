<?php

namespace Tests\Unit\Containers\Finance\Foundation\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Database\QueryException;

class AccountTest extends TestCase
{
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        app()->instance('current.company_id', $this->company->id);
    }

    public function testAccountCanBeCreated(): void
    {
        $account = Account::factory()->create([
            'code' => '1001',
            'name' => 'Cash',
            'element_type' => 'asset',
            'balance_direction' => 'debit',
        ]);

        $this->assertDatabaseHas('accounts', [
            'code' => '1001',
            'name' => 'Cash',
            'company_id' => $this->company->id,
        ]);
    }

    public function testAccountHasCorrectDefaults(): void
    {
        $account = Account::factory()->create([
            'code' => '1002',
            'name' => 'Bank',
        ]);

        $this->assertTrue($account->is_detail);
        $this->assertTrue($account->is_active);
        $this->assertFalse($account->has_aux);
        $this->assertNull($account->parent_id);
        $this->assertEquals(1, $account->level);
    }

    public function testAccountCodeMustBeUniquePerCompany(): void
    {
        Account::factory()->create([
            'code' => 'DUPLICATE',
            'company_id' => $this->company->id,
        ]);

        $this->expectException(QueryException::class);
        Account::factory()->create([
            'code' => 'DUPLICATE',
            'company_id' => $this->company->id,
        ]);
    }

    public function testAccountCodeCanBeDuplicateAcrossDifferentCompanies(): void
    {
        $company2 = Company::factory()->create();

        Account::factory()->create([
            'code' => 'SHARED',
            'company_id' => $this->company->id,
        ]);

        app()->instance('current.company_id', $company2->id);
        $account2 = Account::factory()->create([
            'code' => 'SHARED',
            'company_id' => $company2->id,
        ]);

        $this->assertDatabaseHas('accounts', [
            'code' => 'SHARED',
            'company_id' => $this->company->id,
        ]);

        $this->assertDatabaseHas('accounts', [
            'code' => 'SHARED',
            'company_id' => $company2->id,
        ]);
    }

    public function testElementTypeMustBeValidEnum(): void
    {
        $this->expectException(QueryException::class);
        Account::factory()->create([
            'element_type' => 'invalid_type',
        ]);
    }

    public function testElementTypeAcceptsAllValidValues(): void
    {
        $validTypes = ['asset', 'liability', 'equity', 'income', 'expense', 'cost'];

        foreach ($validTypes as $index => $type) {
            $account = Account::factory()->create([
                'code' => '100' . $index,
                'element_type' => $type,
            ]);

            $this->assertEquals($type, $account->element_type);
        }
    }

    public function testBalanceDirectionMustBeValidEnum(): void
    {
        $this->expectException(QueryException::class);
        Account::factory()->create([
            'balance_direction' => 'invalid_direction',
        ]);
    }

    public function testBalanceDirectionAcceptsDebitAndCredit(): void
    {
        $debitAccount = Account::factory()->create([
            'code' => '1001',
            'balance_direction' => 'debit',
        ]);

        $creditAccount = Account::factory()->create([
            'code' => '2001',
            'balance_direction' => 'credit',
        ]);

        $this->assertEquals('debit', $debitAccount->balance_direction);
        $this->assertEquals('credit', $creditAccount->balance_direction);
    }

    public function testAccountCanHaveParent(): void
    {
        $parent = Account::factory()->create([
            'code' => '1000',
            'name' => 'Assets',
            'level' => 1,
        ]);

        $child = Account::factory()->create([
            'code' => '1001',
            'name' => 'Current Assets',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertInstanceOf(Account::class, $child->parent);
        $this->assertEquals('Assets', $child->parent->name);
    }

    public function testAccountCanHaveChildren(): void
    {
        $parent = Account::factory()->create([
            'code' => '1000',
            'name' => 'Assets',
            'level' => 1,
        ]);

        $child1 = Account::factory()->create([
            'code' => '1001',
            'name' => 'Current Assets',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        $child2 = Account::factory()->create([
            'code' => '1002',
            'name' => 'Fixed Assets',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        $parent->refresh();
        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains($child1));
        $this->assertTrue($parent->children->contains($child2));
    }

    public function testParentIsDetailAutoUpdatedToFalseWhenChildCreated(): void
    {
        $parent = Account::factory()->create([
            'code' => '1000',
            'name' => 'Assets',
            'level' => 1,
            'is_detail' => true,
        ]);

        $this->assertTrue($parent->is_detail);

        Account::factory()->create([
            'code' => '1001',
            'name' => 'Current Assets',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        $parent->refresh();
        $this->assertFalse($parent->is_detail);
    }

    public function testParentIdMustReferenceExistingAccount(): void
    {
        $this->expectException(QueryException::class);
        Account::factory()->create([
            'code' => '1001',
            'parent_id' => 99999, // Non-existent ID
        ]);
    }

    public function testAccountBelongsToCompanyTraitAutoFillsCompanyId(): void
    {
        $account = Account::factory()->create([
            'code' => '1001',
            'name' => 'Test Account',
        ]);

        $this->assertEquals($this->company->id, $account->company_id);
    }

    public function testAccountBelongsToCompanyTraitFiltersQueriesByCompanyId(): void
    {
        $company2 = Company::factory()->create();

        Account::factory()->create([
            'code' => 'COMP1',
            'company_id' => $this->company->id,
        ]);

        app()->instance('current.company_id', $company2->id);
        Account::factory()->create([
            'code' => 'COMP2',
            'company_id' => $company2->id,
        ]);

        // Query should only return accounts for company2
        $accounts = Account::all();
        $this->assertCount(1, $accounts);
        $this->assertEquals('COMP2', $accounts->first()->code);
    }

    public function testAccountCanBeInactive(): void
    {
        $account = Account::factory()->create([
            'code' => '1001',
            'is_active' => false,
        ]);

        $this->assertFalse($account->is_active);
    }

    public function testAccountCanHaveAuxiliaryAccounting(): void
    {
        $account = Account::factory()->create([
            'code' => '1001',
            'has_aux' => true,
        ]);

        $this->assertTrue($account->has_aux);
    }

    public function testAccountLevelIsCastToInteger(): void
    {
        $account = Account::factory()->create([
            'code' => '1001',
            'level' => '3',
        ]);

        $this->assertIsInt($account->level);
        $this->assertEquals(3, $account->level);
    }

    public function testBooleanFieldsAreCastCorrectly(): void
    {
        $account = Account::factory()->create([
            'code' => '1001',
            'is_detail' => 1,
            'is_active' => 1,
            'has_aux' => 0,
        ]);

        $this->assertIsBool($account->is_detail);
        $this->assertIsBool($account->is_active);
        $this->assertIsBool($account->has_aux);
        $this->assertTrue($account->is_detail);
        $this->assertTrue($account->is_active);
        $this->assertFalse($account->has_aux);
    }
}
