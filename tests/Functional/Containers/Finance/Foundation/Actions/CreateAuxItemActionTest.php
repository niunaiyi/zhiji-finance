<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\CreateAuxItemAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Ship\Parents\Tests\TestCase;

class CreateAuxItemActionTest extends TestCase
{
    private CreateAuxItemAction $action;
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

        $this->action = app(CreateAuxItemAction::class);
    }

    public function testCreateAuxItemSuccessfully(): void
    {
        $data = [
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CUST001',
            'name' => 'Test Customer',
            'is_active' => true,
        ];

        $auxItem = $this->action->run($data);

        $this->assertInstanceOf(AuxItem::class, $auxItem);
        $this->assertEquals('CUST001', $auxItem->code);
        $this->assertEquals('Test Customer', $auxItem->name);
        $this->assertEquals($this->auxCategory->id, $auxItem->aux_category_id);
        $this->assertTrue($auxItem->is_active);
        $this->assertEquals($this->company->id, $auxItem->company_id);
    }

    public function testCreateAuxItemWithParent(): void
    {
        $parent = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'PARENT',
            'name' => 'Parent Item',
        ]);

        $data = [
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CHILD001',
            'name' => 'Child Item',
            'parent_id' => $parent->id,
            'is_active' => true,
        ];

        $auxItem = $this->action->run($data);

        $this->assertEquals($parent->id, $auxItem->parent_id);
    }

    public function testCreateAuxItemWithExtra(): void
    {
        $data = [
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CUST002',
            'name' => 'Customer with Extra',
            'extra' => ['phone' => '123456789', 'address' => 'Test Address'],
            'is_active' => true,
        ];

        $auxItem = $this->action->run($data);

        $this->assertEquals(['phone' => '123456789', 'address' => 'Test Address'], $auxItem->extra);
    }

    public function testCreateAuxItemValidatesUniqueCode(): void
    {
        AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CUST001',
            'name' => 'Existing Customer',
        ]);

        $this->expectException(\Apiato\Core\Repositories\Exceptions\ResourceCreationFailed::class);

        $this->action->run([
            'aux_category_id' => $this->auxCategory->id,
            'code' => 'CUST001',
            'name' => 'Duplicate Customer',
            'is_active' => true,
        ]);
    }
}
