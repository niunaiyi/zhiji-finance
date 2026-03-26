<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Constants\CASAccountTemplate;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Seeders\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds standard Chinese chart of accounts (新会计准则) for the default company.
 *
 * Creates 81 level-1 accounts covering all major categories:
 * - Assets (1xxx): 41 accounts
 * - Liabilities (2xxx): 17 accounts
 * - Equity (4xxx): 5 accounts
 * - Cost (5xxx): 4 accounts
 * - Income (6xxx): 5 accounts
 * - Expense (6xxx): 9 accounts
 *
 * Depends on: CompanySeeder
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $company = Company::where('code', 'DEFAULT')->first();

            if (!$company) {
                return;
            }

            $accounts = CASAccountTemplate::ACCOUNTS;

            foreach ($accounts as $accountData) {
                Account::firstOrCreate(
                    [
                        'company_id' => $company->id,
                        'code' => $accountData['code'],
                    ],
                    [
                        'name' => $accountData['name'],
                        'level' => 1,
                        'element_type' => $accountData['element_type'],
                        'balance_direction' => $accountData['balance_direction'],
                        'is_detail' => true,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
