<?php

namespace App\Containers\Finance\Foundation\Data\Seeders;

use App\Containers\Finance\Auth\Models\Company;
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

            $accounts = [
                // Assets (1xxx)
                ['code' => '1001', 'name' => '库存现金', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1002', 'name' => '银行存款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1012', 'name' => '其他货币资金', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1101', 'name' => '交易性金融资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1121', 'name' => '应收票据', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1122', 'name' => '应收账款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1123', 'name' => '预付账款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1221', 'name' => '其他应收款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1231', 'name' => '坏账准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1401', 'name' => '材料采购', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1402', 'name' => '在途物资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1403', 'name' => '原材料', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1404', 'name' => '材料成本差异', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1405', 'name' => '库存商品', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1406', 'name' => '发出商品', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1407', 'name' => '商品进销差价', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1408', 'name' => '委托加工物资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1411', 'name' => '周转材料', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1471', 'name' => '存货跌价准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1501', 'name' => '持有至到期投资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1502', 'name' => '持有至到期投资减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1503', 'name' => '可供出售金融资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1511', 'name' => '长期股权投资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1512', 'name' => '长期股权投资减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1521', 'name' => '投资性房地产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1531', 'name' => '长期应收款', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1601', 'name' => '固定资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1602', 'name' => '累计折旧', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1603', 'name' => '固定资产减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1604', 'name' => '在建工程', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1605', 'name' => '工程物资', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1606', 'name' => '固定资产清理', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1701', 'name' => '无形资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1702', 'name' => '累计摊销', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1703', 'name' => '无形资产减值准备', 'element_type' => 'asset', 'balance_direction' => 'credit'],
                ['code' => '1711', 'name' => '商誉', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1801', 'name' => '长期待摊费用', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1811', 'name' => '递延所得税资产', 'element_type' => 'asset', 'balance_direction' => 'debit'],
                ['code' => '1901', 'name' => '待处理财产损溢', 'element_type' => 'asset', 'balance_direction' => 'debit'],

                // Liabilities (2xxx)
                ['code' => '2001', 'name' => '短期借款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2201', 'name' => '应付票据', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2202', 'name' => '应付账款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2203', 'name' => '预收账款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2211', 'name' => '应付职工薪酬', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2221', 'name' => '应交税费', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2231', 'name' => '应付利息', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2232', 'name' => '应付股利', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2241', 'name' => '其他应付款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2501', 'name' => '长期借款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2502', 'name' => '应付债券', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2701', 'name' => '长期应付款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2702', 'name' => '未确认融资费用', 'element_type' => 'liability', 'balance_direction' => 'debit'],
                ['code' => '2711', 'name' => '专项应付款', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2801', 'name' => '预计负债', 'element_type' => 'liability', 'balance_direction' => 'credit'],
                ['code' => '2901', 'name' => '递延所得税负债', 'element_type' => 'liability', 'balance_direction' => 'credit'],

                // Equity (4xxx)
                ['code' => '4001', 'name' => '实收资本', 'element_type' => 'equity', 'balance_direction' => 'credit'],
                ['code' => '4002', 'name' => '资本公积', 'element_type' => 'equity', 'balance_direction' => 'credit'],
                ['code' => '4101', 'name' => '盈余公积', 'element_type' => 'equity', 'balance_direction' => 'credit'],
                ['code' => '4103', 'name' => '本年利润', 'element_type' => 'equity', 'balance_direction' => 'credit'],
                ['code' => '4104', 'name' => '利润分配', 'element_type' => 'equity', 'balance_direction' => 'credit'],

                // Cost (5xxx)
                ['code' => '5001', 'name' => '生产成本', 'element_type' => 'cost', 'balance_direction' => 'debit'],
                ['code' => '5101', 'name' => '制造费用', 'element_type' => 'cost', 'balance_direction' => 'debit'],
                ['code' => '5201', 'name' => '劳务成本', 'element_type' => 'cost', 'balance_direction' => 'debit'],
                ['code' => '5301', 'name' => '研发支出', 'element_type' => 'cost', 'balance_direction' => 'debit'],

                // Income (6xxx - revenue)
                ['code' => '6001', 'name' => '主营业务收入', 'element_type' => 'income', 'balance_direction' => 'credit'],
                ['code' => '6051', 'name' => '其他业务收入', 'element_type' => 'income', 'balance_direction' => 'credit'],
                ['code' => '6101', 'name' => '公允价值变动损益', 'element_type' => 'income', 'balance_direction' => 'credit'],
                ['code' => '6111', 'name' => '投资收益', 'element_type' => 'income', 'balance_direction' => 'credit'],
                ['code' => '6301', 'name' => '营业外收入', 'element_type' => 'income', 'balance_direction' => 'credit'],

                // Expense (6xxx - expenses)
                ['code' => '6401', 'name' => '主营业务成本', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6402', 'name' => '其他业务成本', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6403', 'name' => '税金及附加', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6601', 'name' => '销售费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6602', 'name' => '管理费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6603', 'name' => '财务费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6701', 'name' => '资产减值损失', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6711', 'name' => '营业外支出', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6801', 'name' => '所得税费用', 'element_type' => 'expense', 'balance_direction' => 'debit'],
                ['code' => '6901', 'name' => '以前年度损益调整', 'element_type' => 'expense', 'balance_direction' => 'debit'],
            ];

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
