<?php

namespace App\Containers\Finance\Foundation\Tests\Functional\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\DetachAuxCategoryFromAccountAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tests\TestCase;

class DetachAuxCategoryFromAccountActionTest extends TestCase
{
    private DetachAuxCategoryFromAccountAction $action;
    private User $user;
    private Company $company;
    private Account $account;
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

        $this->account = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1122',
            'name' => 'Accounts Receivable',
            'has_aux' => true,
        ]);

        $this->auxCategory = AuxCategory::factory()->create([
            'company_id' => $this->company->id,
            'code' => 'customer',
            'name' => 'Customer',
        ]);

        $this->action = app(DetachAuxCategoryFromAccountAction::class);
    }

    public function testDetachAuxCategoryFromAccountSuccessfully(): void
    {
        // First attach the category
        $this->account->auxCategories()->attach($this->auxCategory->id, [
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->assertTrue($this->account->auxCategories()->where('aux_category_id', $this->auxCategory->id)->exists());

        // Now detach
        $result = $this->action->run([
            'account_id' => $this->account->id,
            'aux_category_id' => $this->auxCategory->id,
        ]);

        $this->assertTrue($result);
        $this->assertFalse($this->account->auxCategories()->where('aux_category_id', $this->auxCategory->id)->exists());
    }

    public function testHandlesNonExistentRelationshipGracefully(): void
    {
        // Ensure no relationship exists
        $this->assertFalse($this->account->auxCategories()->where('aux_category_id', $this->auxCategory->id)->exists());

        // Attempt to detach non-existent relationship
        $result = $this->action->run([
            'account_id' => $this->account->id,
            'aux_category_id' => $this->auxCategory->id,
        ]);

        // Should return true (Laravel's detach returns the number of affected rows, 0 in this case)
        $this->assertTrue($result);
    }
}
