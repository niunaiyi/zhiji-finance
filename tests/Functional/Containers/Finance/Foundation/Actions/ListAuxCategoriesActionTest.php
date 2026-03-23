<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\ListAuxCategoriesAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tests\TestCase;

class ListAuxCategoriesActionTest extends TestCase
{
    private ListAuxCategoriesAction $action;
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

        $this->action = app(ListAuxCategoriesAction::class);
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
