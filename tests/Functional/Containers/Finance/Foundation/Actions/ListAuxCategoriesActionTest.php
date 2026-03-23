<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Actions\ListAuxCategoriesAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Support\Facades\Auth;

class ListAuxCategoriesActionTest extends ShipTestCase
{
    private ListAuxCategoriesAction $action;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(ListAuxCategoriesAction::class);
        $this->company = Company::factory()->create();
        Auth::shouldReceive('user->company_id')->andReturn($this->company->id);
    }

    public function testListAuxCategoriesWithPagination(): void
    {
        AuxCategory::factory()->count(15)->create([
            'company_id' => $this->company->id,
        ]);

        $result = $this->action->run([]);

        $this->assertCount(15, $result->items());
        $this->assertEquals(15, $result->total());
    }

    public function testListAuxCategoriesFilterByCode(): void
    {
        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'DEPT',
            'name' => 'Department',
        ]);

        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'PROJ',
            'name' => 'Project',
        ]);

        $result = $this->action->run(['search' => 'code:DEPT']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('DEPT', $result->items()[0]->code);
    }

    public function testListAuxCategoriesFilterByIsSystem(): void
    {
        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'customer',
            'name' => 'Customer',
            'is_system' => true,
        ]);

        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'CUSTOM',
            'name' => 'Custom',
            'is_system' => false,
        ]);

        $result = $this->action->run(['search' => 'is_system:1']);

        $this->assertCount(1, $result->items());
        $this->assertTrue($result->items()[0]->is_system);
    }

    public function testListAuxCategoriesFilterByName(): void
    {
        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'DEPT',
            'name' => 'Department',
        ]);

        AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'PROJ',
            'name' => 'Project',
        ]);

        $result = $this->action->run(['search' => 'name:Department']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Department', $result->items()[0]->name);
    }
}
