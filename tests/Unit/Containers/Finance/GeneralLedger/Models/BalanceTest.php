<?php

namespace Tests\Unit\Containers\Finance\GeneralLedger\Models;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\GeneralLedger\Models\Balance;
use App\Ship\Parents\Tests\TestCase;

class BalanceTest extends TestCase
{
    private Company $company;
    private Period $period;
    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->company = Company::factory()->create();
        app()->instance('current.company_id', $this->company->id);
        
        $this->period = Period::factory()->create(['company_id' => $this->company->id]);
        $this->account = Account::factory()->create(['company_id' => $this->company->id]);
    }

    public function testBalanceCanBeCreated(): void
    {
        $balance = Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
            'opening_debit' => 1000.00,
            'opening_credit' => 0.00,
            'period_debit' => 500.00,
            'period_credit' => 200.00,
            'closing_debit' => 1300.00,
            'closing_credit' => 0.00,
        ]);

        $this->assertDatabaseHas('balances', [
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
            'opening_debit' => 1000.00,
        ]);
    }

    public function testBalanceBelongsToPeriod(): void
    {
        $balance = Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
        ]);

        $this->assertInstanceOf(Period::class, $balance->period);
        $this->assertEquals($this->period->id, $balance->period->id);
    }

    public function testBalanceBelongsToAccount(): void
    {
        $balance = Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
        ]);

        $this->assertInstanceOf(Account::class, $balance->account);
        $this->assertEquals($this->account->id, $balance->account->id);
    }

    public function testScopeByPeriod(): void
    {
        $period2 = Period::factory()->create(['company_id' => $this->company->id]);
        
        Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
        ]);

        Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $period2->id,
            'account_id' => $this->account->id,
        ]);

        $balances = Balance::byPeriod($this->period->id)->get();
        $this->assertCount(1, $balances);
        $this->assertEquals($this->period->id, $balances->first()->period_id);
    }

    public function testScopeByAccount(): void
    {
        $account2 = Account::factory()->create(['company_id' => $this->company->id]);
        
        Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $this->account->id,
        ]);

        Balance::create([
            'company_id' => $this->company->id,
            'period_id' => $this->period->id,
            'account_id' => $account2->id,
        ]);

        $balances = Balance::byAccount($this->account->id)->get();
        $this->assertCount(1, $balances);
        $this->assertEquals($this->account->id, $balances->first()->account_id);
    }
}
