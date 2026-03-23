<?php

namespace App\Containers\Finance\Voucher\Actions;

use App\Containers\Finance\Voucher\Models\Voucher;
use App\Containers\Finance\Voucher\Models\VoucherLine;
use App\Containers\Finance\Voucher\Models\VoucherLineAux;
use App\Containers\Finance\Voucher\Tasks\CheckPeriodStatusTask;
use App\Containers\Finance\Voucher\Tasks\GenerateVoucherNoTask;
use App\Containers\Finance\Voucher\Tasks\ValidateDetailAccountTask;
use App\Containers\Finance\Voucher\Tasks\ValidateVoucherBalanceTask;
use App\Ship\Parents\Actions\Action;
use Illuminate\Support\Facades\DB;

class CreateVoucherAction extends Action
{
    public function run(array $data): Voucher
    {
        // 校验期间状态
        app(CheckPeriodStatusTask::class)->run($data['period_id']);

        // 校验借贷平衡
        if (!app(ValidateVoucherBalanceTask::class)->run($data['lines'])) {
            throw new \Exception('Voucher is not balanced');
        }

        // 校验末级科目
        $accountIds = array_column($data['lines'], 'account_id');
        if (!app(ValidateDetailAccountTask::class)->run($accountIds)) {
            throw new \Exception('Only detail accounts can be used');
        }

        return DB::transaction(function () use ($data) {
            $companyId = app('current.company_id');

            // 生成凭证号
            $voucherNo = app(GenerateVoucherNoTask::class)->run(
                $companyId,
                $data['period_id'],
                $data['voucher_type']
            );

            // 计算借贷合计
            $totalDebit = '0.00';
            $totalCredit = '0.00';
            foreach ($data['lines'] as $line) {
                $totalDebit = bcadd($totalDebit, $line['debit'] ?? '0.00', 2);
                $totalCredit = bcadd($totalCredit, $line['credit'] ?? '0.00', 2);
            }

            // 创建凭证
            $voucher = Voucher::create([
                'company_id' => $companyId,
                'period_id' => $data['period_id'],
                'voucher_type' => $data['voucher_type'],
                'voucher_no' => $voucherNo,
                'voucher_date' => $data['voucher_date'],
                'summary' => $data['summary'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'source_type' => $data['source_type'] ?? 'manual',
                'source_id' => $data['source_id'] ?? null,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            // 创建凭证行
            foreach ($data['lines'] as $index => $lineData) {
                $line = VoucherLine::create([
                    'company_id' => $companyId,
                    'voucher_id' => $voucher->id,
                    'line_no' => $index + 1,
                    'account_id' => $lineData['account_id'],
                    'summary' => $lineData['summary'] ?? null,
                    'debit' => $lineData['debit'] ?? '0.00',
                    'credit' => $lineData['credit'] ?? '0.00',
                ]);

                // 创建辅助核算
                if (!empty($lineData['aux_items'])) {
                    foreach ($lineData['aux_items'] as $auxItem) {
                        VoucherLineAux::create([
                            'voucher_line_id' => $line->id,
                            'aux_category_id' => $auxItem['aux_category_id'],
                            'aux_item_id' => $auxItem['aux_item_id'],
                        ]);
                    }
                }
            }

            return $voucher->load('lines.auxItems');
        });
    }
}
