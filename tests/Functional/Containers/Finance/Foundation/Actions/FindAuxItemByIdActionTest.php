<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\FindAuxItemByIdAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindAuxItemByIdActionTest extends TestCase
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

    public function testFindAuxItemByIdReturnsItem(): void
    {
        // Arrange
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $item = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'CUST001',
            'name' => 'Test Customer',
        ]);

        // Act
        $action = app(FindAuxItemByIdAction::class);
        $found = $action->run($item->id);

        // Assert
        $this->assertEquals($item->id, $found->id);
        $this->assertEquals('CUST001', $found->code);
        $this->assertEquals('Test Customer', $found->name);
    }

    public function testFindAuxItemByIdEagerLoadsRelationships(): void
    {
        // Arrange
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $parent = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'PARENT',
        ]);

        $item = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'ITEM',
            'parent_id' => $parent->id,
        ]);

        $child = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'code' => 'CHILD',
            'parent_id' => $item->id,
        ]);

        // Act
        $action = app(FindAuxItemByIdAction::class);
        $found = $action->run($item->id);

        // Assert
        $this->assertTrue($found->relationLoaded('auxCategory'));
        $this->assertTrue($found->relationLoaded('parent'));
        $this->assertTrue($found->relationLoaded('children'));

        $this->assertEquals($category->id, $found->auxCategory->id);
        $this->assertEquals($parent->id, $found->parent->id);
        $this->assertCount(1, $found->children);
        $this->assertEquals($child->id, $found->children->first()->id);
    }

    public function testFindAuxItemByIdThrowsExceptionWhenNotFound(): void
    {
        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $action = app(FindAuxItemByIdAction::class);
        $action->run(99999);
    }

    public function testFindAuxItemByIdRespectsMultiTenancy(): void
    {
        // Arrange - Create item in different company
        $otherCompany = Company::factory()->create();

        $otherCategory = AuxCategory::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $otherItem = AuxItem::factory()->create([
            'company_id' => $otherCompany->id,
            'aux_category_id' => $otherCategory->id,
        ]);

        // Act & Assert - Should not find item from other company
        $this->expectException(ModelNotFoundException::class);

        $action = app(FindAuxItemByIdAction::class);
        $action->run($otherItem->id);
    }

    public function testFindAuxItemByIdWithNoParent(): void
    {
        // Arrange - Root item with no parent
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $item = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
            'parent_id' => null,
        ]);

        // Act
        $action = app(FindAuxItemByIdAction::class);
        $found = $action->run($item->id);

        // Assert
        $this->assertTrue($found->relationLoaded('parent'));
        $this->assertNull($found->parent);
    }

    public function testFindAuxItemByIdWithNoChildren(): void
    {
        // Arrange - Leaf item with no children
        $category = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $item = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $category->id,
        ]);

        // Act
        $action = app(FindAuxItemByIdAction::class);
        $found = $action->run($item->id);

        // Assert
        $this->assertTrue($found->relationLoaded('children'));
        $this->assertCount(0, $found->children);
    }
}
