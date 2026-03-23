<?php

namespace Tests\Functional\Containers\Finance\Foundation\Actions;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Actions\UpdateAuxCategoryAction;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UpdateAuxCategoryActionTest extends ShipTestCase
{
    private UpdateAuxCategoryAction $action;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(UpdateAuxCategoryAction::class);
        $this->company = Company::factory()->create();
        Auth::shouldReceive('user->company_id')->andReturn($this->company->id);
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
