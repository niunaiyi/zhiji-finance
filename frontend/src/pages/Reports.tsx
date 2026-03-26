import React from 'react';
import { Tabs } from 'antd';
import type { TabsProps } from 'antd';
import { FileSearchOutlined, PieChartOutlined, LineChartOutlined } from '@ant-design/icons';
import TrialBalanceTable from '../components/TrialBalanceTable';
import BalanceSheetTable from '../components/BalanceSheetTable';
import IncomeStatementTable from '../components/IncomeStatementTable';

const Reports: React.FC = () => {
    const items: TabsProps['items'] = [
        {
            key: '1',
            label: (
                <span className="flex items-center gap-2">
                    <FileSearchOutlined />
                    科目余额表
                </span>
            ),
            children: <TrialBalanceTable />,
        },
        {
            key: '2',
            label: (
                <span className="flex items-center gap-2">
                    <PieChartOutlined />
                    资产负债表
                </span>
            ),
            children: <BalanceSheetTable />,
        },
        {
            key: '3',
            label: (
                <span className="flex items-center gap-2">
                    <LineChartOutlined />
                    利润表
                </span>
            ),
            children: <IncomeStatementTable />,
        },
    ];

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-bold text-slate-200">报表中心</h2>
                <p className="text-slate-400">查看和分析财务报表数据</p>
            </div>

            <Tabs defaultActiveKey="1" items={items} className="custom-tabs" />
        </div>
    );
};

export default Reports;
