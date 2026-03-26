<?php

namespace Tests\Unit\Containers\Finance\Foundation\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Database\QueryException;

class AuxItemTest extends TestCase
{
    private Company $company;
    private AuxCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        app()->instance('current.company_id', $this->company->id);
        $this->category = AuxCategory::factory()->create();
    }

    public function testAuxItemCanBeCreated(): void
    {
        $item = AuxItem::factory()->create([
            'aux_category_id' => $this->category->id,
            'code' => 'IT01',
            'name' => 'Department A',
        ]);

        $this->assertDatabaseHas('aux_items', [
            'code' => 'IT01',
            'name' => 'Department A',
            'company_id' => $this->company->id,
            'aux_category_id' => $this->category->id,
        ]);
    }

    public function testAuxItemBelongsToCategory(): void
    {
        $item = AuxItem::factory()->create(['aux_category_id' => $this->category->id]);
        $this->assertInstanceOf(AuxCategory::class, $item->auxCategory);
        $this->assertEquals($this->category->id, $item->auxCategory->id);
    }

    public function testAuxItemCanHaveParentAndChildren(): void
    {
        $parent = AuxItem::factory()->create(['aux_category_id' => $this->category->id, 'code' => 'P1']);
        $child = AuxItem::factory()->create(['aux_category_id' => $this->category->id, 'code' => 'C1', 'parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertInstanceOf(AuxItem::class, $child->parent);
        $this->assertTrue($parent->children->contains($child));
    }

    public function testAuxItemCodeMustBeUniquePerCategoryPerCompany(): void
    {
        AuxItem::factory()->create([
            'aux_category_id' => $this->category->id,
            'code' => 'DUPLICATE',
        ]);

        $this->expectException(QueryException::class);
        AuxItem::factory()->create([
            'aux_category_id' => $this->category->id,
            'code' => 'DUPLICATE',
        ]);
    }

    public function testAuxItemCanHaveExtraData(): void
    {
        $extra = ['key' => 'value', 'nested' => ['a' => 1]];
        $item = AuxItem::factory()->create([
            'aux_category_id' => $this->category->id,
            'extra' => $extra,
        ]);

        $this->assertEquals($extra, $item->extra);
    }

    public function testBelongsToCompanyTraitAutoFillsCompanyId(): void
    {
        $item = AuxItem::factory()->create(['aux_category_id' => $this->category->id]);
        $this->assertEquals($this->company->id, $item->company_id);
    }
}
