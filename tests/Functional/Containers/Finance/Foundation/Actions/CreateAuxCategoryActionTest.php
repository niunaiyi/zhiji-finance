<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Actions\CreateAuxCategoryAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Support\Facades\Auth;

class CreateAuxCategoryActionTest extends ShipTestCase
{
    private CreateAuxCategoryAction $action;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(CreateAuxCategoryAction::class);
        $this->company = Company::factory()->create();
        Auth::shouldReceive('user->company_id')->andReturn($this->company->id);
    }

    public function testCreateAuxCategorySuccessfully(): void
    {
        $data = [
            'code' => 'DEPT',
            'name' => 'Department',
            'is_system' => false,
        ];

        $auxCategory = $this->action->run($data);

        $this->assertInstanceOf(AuxCategory::class, $auxCategory);
        $this->assertEquals('DEPT', $auxCategory->code);
        $this->assertEquals('Department', $auxCategory->name);
        $this->assertFalse($auxCategory->is_system);
        $this->assertEquals($this->company->id, $auxCategory->company_id);
    }

    public function testCreateAuxCategoryWithSystemFlag(): void
    {
        $data = [
            'code' => 'CUSTOM',
            'name' => 'Custom Category',
            'is_system' => true,
        ];

        $auxCategory = $this->action->run($data);

        $this->assertTrue($auxCategory->is_system);
    }

    public function testCreateAuxCategoryValidatesUniqueCode(): void
    {
        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'DEPT',
            'name' => 'Department',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->action->run([
            'code' => 'DEPT',
            'name' => 'Another Department',
            'is_system' => false,
        ]);
    }
}
