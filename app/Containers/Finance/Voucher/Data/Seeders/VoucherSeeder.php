<?php

namespace App\Containers\Finance\Voucher\Data\Seeders;

use App\Containers\AppSection\User\Models\User;
use App\Containers\Finance\Auth\Models\Company;
use App\Containers\Finance\Foundation\Models\Account;
use App\Containers\Finance\Foundation\Models\Period;
use App\Containers\Finance\Voucher\Models\Voucher;
use App\Containers\Finance\Voucher\Models\VoucherLine;
use App\Ship\Parents\Seeders\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VoucherSeeder
 *
 * Seeds 20 realistic accounting vouchers for 2026 Q1 (periods 1-3).
 * Covers common business scenarios: sales, purchases, payroll, expenses, bank transfers.
 *
 * All vouchers are balanced (debit = credit).
 * Status is 'posted' for historical periods, 'draft'/'reviewed' for current period.
 *
 * Depends on: CompanySeeder, AccountSeeder, PeriodSeeder
 */
class VoucherSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $company = Company::where('code', 'DEFAULT')->first();
            if (!$company) return;

            $admin = User::where('email', 'admin@example.com')->first();
            if (!$admin) return;

            // Load accounts by code for easy reference
            $accounts = Account::where('company_id', $company->id)
                ->get()
                ->keyBy('code');

            // Load periods 1-3 (Jan, Feb, Mar 2026)
            $periods = Period::where('company_id', $company->id)
                ->where('fiscal_year', 2026)
                ->whereIn('period_number', [1, 2, 3])
                ->get()
                ->keyBy('period_number');

            if ($periods->isEmpty()) return;

            $voucherData = [
                // ── January (Period 1) ─────────────────────────────────────────
                [
                    'period' => 1, 'date' => '2026-01-05', 'no' => '2026-记-0001',
                    'type' => 'receipt', 'status' => 'posted',
                    'summary' => '收到客户甲货款',
                    'lines' => [
                        ['code' => '1002', 'debit' => 58000.00, 'credit' => 0, 'remark' => '银行存款'],
                        ['code' => '1122', 'debit' => 0, 'credit' => 58000.00, 'remark' => '应收账款-客户甲'],
                    ],
                ],
                [
                    'period' => 1, 'date' => '2026-01-08', 'no' => '2026-记-0002',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '销售商品A，开具增值税发票',
                    'lines' => [
                        ['code' => '1122', 'debit' => 113000.00, 'credit' => 0, 'remark' => '含税销售额'],
                        ['code' => '6001', 'debit' => 0, 'credit' => 100000.00, 'remark' => '主营业务收入'],
                        ['code' => '2221', 'debit' => 0, 'credit' => 13000.00, 'remark' => '应交增值税(销项)'],
                    ],
                ],
                [
                    'period' => 1, 'date' => '2026-01-10', 'no' => '2026-记-0003',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '结转销售商品A成本',
                    'lines' => [
                        ['code' => '6401', 'debit' => 65000.00, 'credit' => 0, 'remark' => '主营业务成本'],
                        ['code' => '1405', 'debit' => 0, 'credit' => 65000.00, 'remark' => '库存商品'],
                    ],
                ],
                [
                    'period' => 1, 'date' => '2026-01-15', 'no' => '2026-记-0004',
                    'type' => 'payment', 'status' => 'posted',
                    'summary' => '支付1月员工工资',
                    'lines' => [
                        ['code' => '2211', 'debit' => 120000.00, 'credit' => 0, 'remark' => '应付职工薪酬'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 120000.00, 'remark' => '银行存款'],
                    ],
                ],
                [
                    'period' => 1, 'date' => '2026-01-15', 'no' => '2026-记-0005',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '计提1月工资',
                    'lines' => [
                        ['code' => '6602', 'debit' => 80000.00, 'credit' => 0, 'remark' => '管理人员工资'],
                        ['code' => '6601', 'debit' => 40000.00, 'credit' => 0, 'remark' => '销售人员工资'],
                        ['code' => '2211', 'debit' => 0, 'credit' => 120000.00, 'remark' => '应付职工薪酬'],
                    ],
                ],
                [
                    'period' => 1, 'date' => '2026-01-20', 'no' => '2026-记-0006',
                    'type' => 'payment', 'status' => 'posted',
                    'summary' => '支付供应商乙货款（采购原材料）',
                    'lines' => [
                        ['code' => '2202', 'debit' => 45200.00, 'credit' => 0, 'remark' => '应付账款-供应商乙'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 45200.00, 'remark' => '银行存款'],
                    ],
                ],
                [
                    'period' => 1, 'date' => '2026-01-22', 'no' => '2026-记-0007',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '采购原材料入库',
                    'lines' => [
                        ['code' => '1403', 'debit' => 40000.00, 'credit' => 0, 'remark' => '原材料'],
                        ['code' => '2221', 'debit' => 5200.00, 'credit' => 0, 'remark' => '应交增值税(进项)'],
                        ['code' => '2202', 'debit' => 0, 'credit' => 45200.00, 'remark' => '应付账款-供应商乙'],
                    ],
                ],

                // ── February (Period 2) ────────────────────────────────────────
                [
                    'period' => 2, 'date' => '2026-02-03', 'no' => '2026-记-0008',
                    'type' => 'receipt', 'status' => 'posted',
                    'summary' => '收到客户乙货款',
                    'lines' => [
                        ['code' => '1002', 'debit' => 226000.00, 'credit' => 0, 'remark' => '银行存款'],
                        ['code' => '1122', 'debit' => 0, 'credit' => 226000.00, 'remark' => '应收账款-客户乙'],
                    ],
                ],
                [
                    'period' => 2, 'date' => '2026-02-06', 'no' => '2026-记-0009',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '销售商品B，开具增值税发票',
                    'lines' => [
                        ['code' => '1122', 'debit' => 226000.00, 'credit' => 0, 'remark' => '含税销售额'],
                        ['code' => '6001', 'debit' => 0, 'credit' => 200000.00, 'remark' => '主营业务收入'],
                        ['code' => '2221', 'debit' => 0, 'credit' => 26000.00, 'remark' => '应交增值税(销项)'],
                    ],
                ],
                [
                    'period' => 2, 'date' => '2026-02-06', 'no' => '2026-记-0010',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '结转销售商品B成本',
                    'lines' => [
                        ['code' => '6401', 'debit' => 130000.00, 'credit' => 0, 'remark' => '主营业务成本'],
                        ['code' => '1405', 'debit' => 0, 'credit' => 130000.00, 'remark' => '库存商品'],
                    ],
                ],
                [
                    'period' => 2, 'date' => '2026-02-10', 'no' => '2026-记-0011',
                    'type' => 'payment', 'status' => 'posted',
                    'summary' => '支付房租（季度）',
                    'lines' => [
                        ['code' => '6602', 'debit' => 36000.00, 'credit' => 0, 'remark' => '管理费用-租金'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 36000.00, 'remark' => '银行存款'],
                    ],
                ],
                [
                    'period' => 2, 'date' => '2026-02-15', 'no' => '2026-记-0012',
                    'type' => 'transfer', 'status' => 'posted',
                    'summary' => '计提2月工资',
                    'lines' => [
                        ['code' => '6602', 'debit' => 82000.00, 'credit' => 0, 'remark' => '管理人员工资'],
                        ['code' => '6601', 'debit' => 43000.00, 'credit' => 0, 'remark' => '销售人员工资'],
                        ['code' => '2211', 'debit' => 0, 'credit' => 125000.00, 'remark' => '应付职工薪酬'],
                    ],
                ],
                [
                    'period' => 2, 'date' => '2026-02-18', 'no' => '2026-记-0013',
                    'type' => 'payment', 'status' => 'posted',
                    'summary' => '支付2月员工工资',
                    'lines' => [
                        ['code' => '2211', 'debit' => 125000.00, 'credit' => 0, 'remark' => '应付职工薪酬'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 125000.00, 'remark' => '银行存款'],
                    ],
                ],
                [
                    'period' => 2, 'date' => '2026-02-25', 'no' => '2026-记-0014',
                    'type' => 'payment', 'status' => 'posted',
                    'summary' => '缴纳增值税',
                    'lines' => [
                        ['code' => '2221', 'debit' => 33800.00, 'credit' => 0, 'remark' => '应交增值税'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 33800.00, 'remark' => '银行存款'],
                    ],
                ],

                // ── March (Period 3) ───────────────────────────────────────────
                [
                    'period' => 3, 'date' => '2026-03-03', 'no' => '2026-记-0015',
                    'type' => 'transfer', 'status' => 'reviewed',
                    'summary' => '销售服务，开具发票',
                    'lines' => [
                        ['code' => '1122', 'debit' => 56500.00, 'credit' => 0, 'remark' => '应收账款'],
                        ['code' => '6001', 'debit' => 0, 'credit' => 50000.00, 'remark' => '主营业务收入'],
                        ['code' => '2221', 'debit' => 0, 'credit' => 6500.00, 'remark' => '应交增值税(销项)'],
                    ],
                ],
                [
                    'period' => 3, 'date' => '2026-03-10', 'no' => '2026-记-0016',
                    'type' => 'payment', 'status' => 'reviewed',
                    'summary' => '购买办公设备',
                    'lines' => [
                        ['code' => '1601', 'debit' => 25000.00, 'credit' => 0, 'remark' => '固定资产-办公设备'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 25000.00, 'remark' => '银行存款'],
                    ],
                ],
                [
                    'period' => 3, 'date' => '2026-03-12', 'no' => '2026-记-0017',
                    'type' => 'transfer', 'status' => 'reviewed',
                    'summary' => '计提3月固定资产折旧',
                    'lines' => [
                        ['code' => '6602', 'debit' => 3500.00, 'credit' => 0, 'remark' => '管理费用-折旧'],
                        ['code' => '1602', 'debit' => 0, 'credit' => 3500.00, 'remark' => '累计折旧'],
                    ],
                ],
                [
                    'period' => 3, 'date' => '2026-03-15', 'no' => '2026-记-0018',
                    'type' => 'transfer', 'status' => 'draft',
                    'summary' => '计提3月工资',
                    'lines' => [
                        ['code' => '6602', 'debit' => 85000.00, 'credit' => 0, 'remark' => '管理人员工资'],
                        ['code' => '6601', 'debit' => 45000.00, 'credit' => 0, 'remark' => '销售人员工资'],
                        ['code' => '2211', 'debit' => 0, 'credit' => 130000.00, 'remark' => '应付职工薪酬'],
                    ],
                ],
                [
                    'period' => 3, 'date' => '2026-03-20', 'no' => '2026-记-0019',
                    'type' => 'receipt', 'status' => 'draft',
                    'summary' => '预收客户丙定金',
                    'lines' => [
                        ['code' => '1002', 'debit' => 30000.00, 'credit' => 0, 'remark' => '银行存款'],
                        ['code' => '2203', 'debit' => 0, 'credit' => 30000.00, 'remark' => '预收账款-客户丙'],
                    ],
                ],
                [
                    'period' => 3, 'date' => '2026-03-25', 'no' => '2026-记-0020',
                    'type' => 'payment', 'status' => 'draft',
                    'summary' => '支付广告费',
                    'lines' => [
                        ['code' => '6601', 'debit' => 15000.00, 'credit' => 0, 'remark' => '销售费用-广告费'],
                        ['code' => '1002', 'debit' => 0, 'credit' => 15000.00, 'remark' => '银行存款'],
                    ],
                ],
            ];

            foreach ($voucherData as $vd) {
                $period = $periods->get($vd['period']);
                if (!$period) continue;

                // Skip if already exists
                if (Voucher::withoutGlobalScopes()->where('company_id', $company->id)->where('voucher_no', $vd['no'])->exists()) {
                    continue;
                }

                $totalDebit  = collect($vd['lines'])->sum('debit');
                $totalCredit = collect($vd['lines'])->sum('credit');

                $voucher = Voucher::create([
                    'company_id'   => $company->id,
                    'period_id'    => $period->id,
                    'voucher_type' => $vd['type'],
                    'voucher_no'   => $vd['no'],
                    'voucher_date' => $vd['date'],
                    'status'       => $vd['status'],
                    'summary'      => $vd['summary'],
                    'total_debit'  => $totalDebit,
                    'total_credit' => $totalCredit,
                    'source_type'  => 'manual',
                    'created_by'   => $admin->id,
                    'reviewed_by'  => in_array($vd['status'], ['reviewed', 'posted']) ? $admin->id : null,
                    'posted_by'    => $vd['status'] === 'posted' ? $admin->id : null,
                    'posted_at'    => $vd['status'] === 'posted' ? now() : null,
                ]);

                foreach ($vd['lines'] as $idx => $line) {
                    $account = $accounts->get($line['code']);
                    if (!$account) continue;

                    VoucherLine::create([
                        'company_id'  => $company->id,
                        'voucher_id'  => $voucher->id,
                        'line_no'     => $idx + 1,
                        'account_id'  => $account->id,
                        'summary'     => $line['remark'],
                        'debit'       => $line['debit'],
                        'credit'      => $line['credit'],
                    ]);
                }
            }
        });
    }
}
