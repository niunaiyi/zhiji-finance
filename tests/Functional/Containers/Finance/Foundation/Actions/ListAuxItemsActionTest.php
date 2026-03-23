<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\ListAuxItemsAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tests\TestCase;

class ListAuxItemsActionTest extends TestCase
{
    private ListAuxItemsAction $action;
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

        $this->action = app(ListAuxItemsAction::class);
    }

    public function testListAuxItemsWithPagination(): void
    {
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        AuxItem::factory()->count(15)->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
        ]);

        $result = $this->action->run([]);

        $this->assertCount(15, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function testListAuxItemsFilterBySearch(): void
    {
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'CUST001',
            'name' => 'Customer One',
        ]);

        AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'CUST002',
            'name' => 'Customer Two',
        ]);

        AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'SUPP001',
            'name' => 'Supplier One',
        ]);

        // Search by code
        $result = $this->action->run(['search' => 'CUST']);
        $this->assertCount(2, $result->items());

        // Search by name
        $result = $this->action->run(['search' => 'Supplier']);
        $this->assertCount(1, $result->items());
        $this->assertEquals('SUPP001', $result->items()[0]->code);
    }

    public function testListAuxItemsFilterByAuxCategoryId(): void
    {
        $category1 = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'customer',
        ]);

        $category2 = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'supplier',
        ]);

        AuxItem::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category1->id,
        ]);

        AuxItem::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category2->id,
        ]);

        $result = $this->action->run(['aux_category_id' => $category1->id]);

        $this->assertCount(3, $result->items());
        foreach ($result->items() as $item) {
            $this->assertEquals($category1->id, $item->aux_category_id);
        }
    }

    public function testListAuxItemsFilterByIsActive(): void
    {
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        AuxItem::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'is_active' => true,
        ]);

        AuxItem::factory()->count(2)->inactive()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
        ]);

        // Filter active items
        $result = $this->action->run(['is_active' => true]);
        $this->assertCount(3, $result->items());
        foreach ($result->items() as $item) {
            $this->assertTrue($item->is_active);
        }

        // Filter inactive items
        $result = $this->action->run(['is_active' => false]);
        $this->assertCount(2, $result->items());
        foreach ($result->items() as $item) {
            $this->assertFalse($item->is_active);
        }
    }

    public function testListAuxItemsRespectsMultiTenancy(): void
    {
        $otherCompany = Company::factory()->create();

        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $otherCategory = AuxCategory::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        // Create items in current company
        AuxItem::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
        ]);

        // Create items in other company
        AuxItem::factory()->count(5)->create([
            'company_id' => $otherCompany->id,
            'aux_category_id' => $otherCategory->id,
        ]);

        $result = $this->action->run([]);

        // Should only return items from current company
        $this->assertCount(3, $result->items());
        foreach ($result->items() as $item) {
            $this->assertEquals($this->company->id, $item->company_id);
        }
    }

    public function testListAuxItemsEagerLoadsRelationships(): void
    {
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $parent = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
        ]);

        $child = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'parent_id' => $parent->id,
        ]);

        $result = $this->action->run([]);

        foreach ($result->items() as $item) {
            $this->assertTrue($item->relationLoaded('auxCategory'));
            $this->assertTrue($item->relationLoaded('parent'));
        }
    }
}
