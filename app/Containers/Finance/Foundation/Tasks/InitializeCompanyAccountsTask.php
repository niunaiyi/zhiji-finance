<?php

namespace App\Containers\Finance\Foundation\Tasks;

use App\Containers\Finance\Foundation\Constants\CASAccountTemplate;
use App\Containers\Finance\Foundation\Models\Account;
use App\Ship\Parents\Tasks\Task;

/**
 * 账套科目初始化任务。
 * 根据 CAS 标准模板为指定公司/账套创建基础一级科目。
 */
class InitializeCompanyAccountsTask extends Task
{
    /**
     * 为指定公司初始化标准会计科目。
     *
     * @param int $companyId 账套ID
     */
    public function run(int $companyId): void
    {
        foreach (CASAccountTemplate::ACCOUNTS as $accountData) {
            Account::firstOrCreate(
                [
                    'company_id' => $companyId,
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
    }
}
