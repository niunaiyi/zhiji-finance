<?php

namespace App\Containers\Finance\Foundation\Tests\Functional\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Models\UserCompanyRole;
use App\Containers\Finance\Foundation\Actions\AttachAuxCategoryToAccountAction;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Ship\Parents\Tests\TestCase;
use Illuminate\Validation\ValidationException;

class AttachAuxCategoryToAccountActionTest extends TestCase
{
    private AttachAuxCategoryToAccountAction $action;
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

        $this->action = app(AttachAuxCategoryToAccountAction::class);
    }

    public function testAttachAuxCategoryToAccountSuccessfully(): void
    {
        $data = [
            'account_id' => $this->account->id,
            'aux_category_id' => $this->auxCategory->id,
            'is_required' => true,
            'sort_order' => 1,
        ];

        $result = $this->action->run($data);

        $this->assertTrue($result);
        $this->assertTrue($this->account->auxCategories()->where('aux_category_id', $this->auxCategory->id)->exists());

        $pivot = $this->account->auxCategories()->where('aux_category_id', $this->auxCategory->id)->first()->pivot;
        $this->assertTrue($pivot->is_required);
        $this->assertEquals(1, $pivot->sort_order);
    }

    public function testThrowsValidationExceptionWhenAccountHasAuxIsFalse(): void
    {
        $accountWithoutAux = Account::factory()->create([
            'company_id' => $this->company->id,
            'code' => '1001',
            'name' => 'Cash',
            'has_aux' => false,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Account does not support auxiliary accounting');

        $this->action->run([
            'account_id' => $accountWithoutAux->id,
            'aux_category_id' => $this->auxCategory->id,
        ]);
    }

    public function testSupportsOptionalParameters(): void
    {
        $data = [
            'account_id' => $this->account->id,
            'aux_category_id' => $this->auxCategory->id,
        ];

        $result = $this->action->run($data);

        $this->assertTrue($result);

        $pivot = $this->account->auxCategories()->where('aux_category_id', $this->auxCategory->id)->first()->pivot;
        $this->assertTrue($pivot->is_required); // default value
        $this->assertEquals(0, $pivot->sort_order); // default value
    }

    public function testPreventsDuplicateAttachments(): void
    {
        // First attachment
        $this->action->run([
            'account_id' => $this->account->id,
            'aux_category_id' => $this->auxCategory->id,
        ]);

        // Attempt duplicate attachment
        $this->expectException(\Exception::class);

        $this->action->run([
            'account_id' => $this->account->id,
            'aux_category_id' => $this->auxCategory->id,
        ]);
    }
}
