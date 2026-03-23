<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\UpdateAuxCategoryAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class UpdateAuxCategoryActionTest extends TestCase
{
    private UpdateAuxCategoryAction $action;
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

        $this->action = app(UpdateAuxCategoryAction::class);
    }

    public function testUpdateAuxCategorySuccessfully(): void
    {
        $auxCategory = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'DEPT',
            'name' => 'Department',
            'is_system' => false,
        ]);

        $updated = $this->action->run($auxCategory->id, [
            'name' => 'Updated Department',
        ]);

        $this->assertEquals('Updated Department', $updated->name);
        $this->assertEquals('DEPT', $updated->code); // code should remain unchanged
    }

    public function testUpdateSystemCategoryThrowsValidationException(): void
    {
        $systemCategory = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'customer',
            'name' => 'Customer',
            'is_system' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('System categories cannot be modified');

        $this->action->run($systemCategory->id, [
            'name' => 'Updated Customer',
        ]);
    }

    public function testUpdateNonSystemCategoryAllowed(): void
    {
        $customCategory = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'CUSTOM',
            'name' => 'Custom Category',
            'is_system' => false,
        ]);

        $updated = $this->action->run($customCategory->id, [
            'name' => 'Updated Custom Category',
        ]);

        $this->assertEquals('Updated Custom Category', $updated->name);
    }
}
