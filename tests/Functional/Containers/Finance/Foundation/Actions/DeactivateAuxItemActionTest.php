<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\DeactivateAuxItemAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class DeactivateAuxItemActionTest extends TestCase
{
    private DeactivateAuxItemAction $action;
    private User $user;
    private Company $company;
    private AuxCategory $auxCategory;

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

        $this->action = app(DeactivateAuxItemAction::class);
    }

    public function testDeactivateAuxItemSuccessfully(): void
    {
        $auxItem = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CUST001',
            'name' => 'Test Customer',
            'is_active' => true,
        ]);

        $deactivated = $this->action->run($auxItem->id);

        $this->assertFalse($deactivated->is_active);
        $this->assertEquals($auxItem->id, $deactivated->id);
    }

    public function testCannotDeactivateAuxItemWithActiveChildren(): void
    {
        $parent = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'PARENT',
            'name' => 'Parent Item',
            'is_active' => true,
        ]);

        AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CHILD001',
            'name' => 'Child Item',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot deactivate aux item with active children');

        $this->action->run($parent->id);
    }

    public function testCanDeactivateAuxItemWithInactiveChildren(): void
    {
        $parent = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'PARENT',
            'name' => 'Parent Item',
            'is_active' => true,
        ]);

        AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CHILD001',
            'name' => 'Child Item',
            'parent_id' => $parent->id,
            'is_active' => false,
        ]);

        $deactivated = $this->action->run($parent->id);

        $this->assertFalse($deactivated->is_active);
    }
}
