<?php

namespace App\Containers\Finance\Payroll\Actions;

use App\Containers\Finance\Foundation\Models\AuxItem;
use App\Containers\Finance\Foundation\Tasks\FindPeriodByYearMonthTask;
use App\Containers\Finance\Payroll\Models\Payroll;
use App\Containers\Finance\Payroll\Models\PayrollLine;
use App\Ship\Parents\Actions\Action;
use App\Containers\Finance\Voucher\Actions\GenerateVoucherFromBusinessAction;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CalculatePayrollAction extends Action
{
    public function run(int $companyId, int $year, int $month): array
    {
        $this->checkRole(['admin', 'accountant']);

        $period = app(FindPeriodByYearMonthTask::class)->run($companyId, $year, $month);

        if (!$period) {
            throw new HttpException(404, '会计期间未找到');
        }

        return DB::transaction(function () use ($companyId, $period, $year, $month) {
            // 1. 创建工资单主表
            $payroll = Payroll::create([
                'company_id' => $companyId,
                'period_id' => $period->id,
                'payroll_no' => 'PAY-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                'payroll_date' => now(),
                'status' => 'draft',
            ]);

            // 2. 获取所有职员 (辅助核算项中的员工分类)
            $category = DB::table('aux_categories')->where('company_id', $companyId)->where('code', 'employee')->first();
            if (!$category instanceof \stdClass) {
                 throw new \Exception('未定义职员分类');
            }

            $employees = AuxItem::where('company_id', $companyId)
                ->where('aux_category_id', $category->id)
                ->where('is_active', true)
                ->get();

            $results = [];

            foreach ($employees as $employee) {
                $baseSalary = (float) ($employee->extra['base_salary'] ?? 0);
                
                PayrollLine::create([
                    'company_id' => $companyId,
                    'payroll_id' => $payroll->id,
                    'employee_id' => $employee->id,
                    'dept_id' => $employee->parent_id,
                    'total_earning' => $baseSalary,
                    'total_deduction' => 0,
                    'net_pay' => $baseSalary,
                ]);

                $results[] = [
                    'employee' => $employee->name,
                    'amount' => $baseSalary,
                ];
            }

            // 3. 生成会计凭证
            $totalAmount = array_sum(array_column($results, 'amount'));
            if ($totalAmount > 0) {
                app(GenerateVoucherFromBusinessAction::class)->run('payroll', $payroll->id, [
                    'period_id'    => $period->id,
                    'voucher_date' => $payroll->payroll_date->format('Y-m-d'),
                    'summary'      => "计提{$year}年{$month}月工资表",
                    'lines'        => [
                        [
                            'account_id' => 74, // 6602 管理费用 (简化逻辑)
                            'debit'      => (string)$totalAmount,
                            'credit'     => '0.00',
                            'summary'    => "计提{$year}年{$month}月工资",
                        ],
                        [
                            'account_id' => 44, // 2211 应付职工薪酬
                            'debit'      => '0.00',
                            'credit'     => (string)$totalAmount,
                            'summary'    => "计提{$year}年{$month}月工资",
                        ]
                    ]
                ]);
            }

            return [
                'payroll' => $payroll->load('lines.employee'),
                'summary' => $results,
            ];
        });
    }
}
