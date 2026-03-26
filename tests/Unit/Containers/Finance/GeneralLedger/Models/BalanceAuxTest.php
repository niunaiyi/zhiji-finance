<?php

namespace Tests\Unit\Containers\Finance\GeneralLedger\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\AuxCategory;
use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\GeneralLedger\Models\BalanceAux;
use App\Ship\Parents\Tests\TestCase;

class BalanceAuxTest extends TestCase
{
    private Company $company;
    private Period $period;
    private Account $account;
    private AuxCategory $category;
    private AuxItem $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        app()->instance('current.company_id', $this->company->id);
        
        $this->period = Period::factory()->create(['company_id' => $this->company->id]);
        $this->account = Account::factory()->create(['company_id' => $this->company->id]);
        $this->category = AuxCategory::factory()->create(['company_id' => $this->company->id]);
        $this->item = AuxItem::factory()->create([
            'company_id' => $this->company->id,
            'aux_category_id' => $this->category->id,
        ]);
    }

    public function testBalanceAuxCanBeCreated(): void
    {
        $balance = BalanceAux::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
            'aux_category_id' => $this->category->id,
            'aux_item_id' => $this->item->id,
            'opening_debit' => 1000.00,
            'closing_debit' => 1000.00,
        ]);

        $this->assertDatabaseHas('balance_aux', [
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
            'aux_item_id' => $this->item->id,
        ]);
    }

    public function testBalanceAuxRelationships(): void
    {
        $balance = BalanceAux::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
            'aux_category_id' => $this->category->id,
            'aux_item_id' => $this->item->id,
        ]);

        $this->assertInstanceOf(Period::class, $balance->period);
        $this->assertInstanceOf(Account::class, $balance->account);
        $this->assertInstanceOf(AuxCategory::class, $balance->category);
        $this->assertInstanceOf(AuxItem::class, $balance->item);
    }
}
