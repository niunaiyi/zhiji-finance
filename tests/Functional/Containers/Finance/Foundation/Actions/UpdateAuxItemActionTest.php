<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\UpdateAuxItemAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tests\TestCase;

class UpdateAuxItemActionTest extends TestCase
{
    private UpdateAuxItemAction $action;
    private User $user;
    private Company $company;
    private AuxCategory $auxCategory;
    private AuxItem $auxItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();

        UserCompanyRole::create([
            'user_id' => $this->user->id,
            'company_id' => $this->company->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($this->user);
        app()->instance('current.company_id', $this->company->id);

        $this->auxCategory = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'customer',
            'name' => 'Customer',
        ]);

        $this->auxItem = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CUST001',
            'name' => 'Original Name',
        ]);

        $this->action = app(UpdateAuxItemAction::class);
    }

    public function testUpdateAuxItemSuccessfully(): void
    {
        $data = [
            'name' => 'Updated Name',
            'is_active' => false,
        ];

        $updated = $this->action->run($this->auxItem->id, $data);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertFalse($updated->is_active);
        $this->assertEquals('CUST001', $updated->code); // Code unchanged
    }

    public function testUpdateAuxItemWithExtra(): void
    {
        $data = [
            'name' => 'Updated Name',
            'extra' => ['phone' => '987654321'],
        ];

        $updated = $this->action->run($this->auxItem->id, $data);

        $this->assertEquals(['phone' => '987654321'], $updated->extra);
    }

    public function testUpdateAuxItemCodeIsImmutable(): void
    {
        $data = [
            'code' => 'NEWCODE',
            'name' => 'Updated Name',
        ];

        $updated = $this->action->run($this->auxItem->id, $data);

        // Code should remain unchanged
        $this->assertEquals('CUST001', $updated->code);
        $this->assertEquals('Updated Name', $updated->name);
    }

    public function testUpdateAuxItemCategoryIsImmutable(): void
    {
        $anotherCategory = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'supplier',
            'name' => 'Supplier',
        ]);

        $data = [
            'aux_category_id' => $anotherCategory->id,
            'name' => 'Updated Name',
        ];

        $updated = $this->action->run($this->auxItem->id, $data);

        // aux_category_id should remain unchanged
        $this->assertEquals($this->auxCategory->id, $updated->aux_category_id);
        $this->assertEquals('Updated Name', $updated->name);
    }

    public function testUpdateAuxItemParent(): void
    {
        $parent = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'PARENT',
            'name' => 'Parent Item',
        ]);

        $data = [
            'name' => 'Updated Name',
            'parent_id' => $parent->id,
        ];

        $updated = $this->action->run($this->auxItem->id, $data);

        $this->assertEquals($parent->id, $updated->parent_id);
    }
}
