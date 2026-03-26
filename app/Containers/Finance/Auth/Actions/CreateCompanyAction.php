<?php

namespace App\Containers\Finance\Auth\Actions;

use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Auth\Tasks\AssignUserRoleTask;
use App\Containers\Finance\Auth\Tasks\CreateCompanyTask;
use App\Containers\Finance\Foundation\Tasks\InitializeCompanyAccountsTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * 创建公司/账套动作。
 * 负责创建一个新的财务账套，并自动初始化其会计科目，同时将当前用户设为该账套的管理员。
 */
class CreateCompanyAction extends Action
{
    public function __construct(
        private readonly CreateCompanyTask $createCompanyTask,
        private readonly AssignUserRoleTask $assignUserRoleTask,
        private readonly InitializeCompanyAccountsTask $initializeCompanyAccountsTask,
    ) {}

    /**
     * 执行账套创建流程。
     * 包含：1. 创建数据库记录；2. 初始化 CAS 标准科目；3. 分配管理员权限。
     *
     * @param array $data 包含 code, name, fiscal_year_start 等数据
     * @return Company
     */
    public function run(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $userId = auth()->id();
            throw_if(!$userId, new \RuntimeException('User must be authenticated'));

            $company = $this->createCompanyTask->run($data);

            $this->initializeCompanyAccountsTask->run($company->id);

            $this->assignUserRoleTask->run(
                $userId,
                $company->id,
                'admin'
            );

            return $company;
        });
    }
}
