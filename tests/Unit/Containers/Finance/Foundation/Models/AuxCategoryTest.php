<?php

namespace Tests\Unit\Containers\Finance\Foundation\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Database\QueryException;

class AuxCategoryTest extends TestCase
{
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        app()->instance('current.company_id', $this->company->id);
    }

    public function testAuxCategoryCanBeCreated(): void
    {
        $category = AuxCategory::factory()->create([
            'code' => 'DEPT',
            'name' => 'Department',
        ]);

        $this->assertDatabaseHas('aux_categories', [
            'code' => 'DEPT',
            'name' => 'Department',
            'company_id' => $this->company->id,
        ]);
    }

    public function testAuxCategoryCodeMustBeUniquePerCompany(): void
    {
        AuxCategory::factory()->create([
            'code' => 'DUPLICATE',
            'company_id' => $this->company->id,
        ]);

        // Note: We need to verify if the migration has unique constraint
        // Based on previous findings, it might be missing.
        $this->expectException(QueryException::class);
        AuxCategory::factory()->create([
            'code' => 'DUPLICATE',
            'company_id' => $this->company->id,
        ]);
    }

    public function testAuxCategoryCanBelongToAccounts(): void
    {
        $category = AuxCategory::factory()->create();
        $account = Account::factory()->create();

        $category->accounts()->attach($account->id, [
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->assertCount(1, $category->accounts);
        $this->assertEquals($account->id, $category->accounts->first()->id);
        $this->assertTrue($category->accounts->first()->pivot->is_required);
        $this->assertEquals(1, $category->accounts->first()->pivot->sort_order);
    }

    public function testBelongsToCompanyTraitAutoFillsCompanyId(): void
    {
        $category = AuxCategory::factory()->create();
        $this->assertEquals($this->company->id, $category->company_id);
    }

    public function testBelongsToCompanyTraitFiltersQueries(): void
    {
        $company2 = Company::factory()->create();
        
        AuxCategory::factory()->create(['code' => 'C1', 'company_id' => $this->company->id]);
        
        app()->instance('current.company_id', $company2->id);
        AuxCategory::factory()->create(['code' => 'C2', 'company_id' => $company2->id]);

        $categories = AuxCategory::all();
        $this->assertCount(1, $categories);
        $this->assertEquals('C2', $categories->first()->code);
    }
}
