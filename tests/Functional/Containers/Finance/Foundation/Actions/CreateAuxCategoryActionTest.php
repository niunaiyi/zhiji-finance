<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\CreateAuxCategoryAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tests\TestCase;

class CreateAuxCategoryActionTest extends TestCase
{
    private CreateAuxCategoryAction $action;
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

        $this->action = app(CreateAuxCategoryAction::class);
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

        // is_system should always be false regardless of input (security fix)
        $this->assertFalse($auxCategory->is_system);
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
