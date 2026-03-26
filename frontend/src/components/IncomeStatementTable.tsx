import React, { useEffect, useState } from 'react';
import { DatePicker, Button, Space, Empty, Spin, Table, Typography } from 'antd';
import type { TableProps } from 'antd';
import { SearchOutlined, LineChartOutlined } from '@ant-design/icons';
import apiClient from '../api/client';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';

const { } = Typography;

interface IncomeRow {
    code: string;
    name: string;
    amount: number;
    type: 'REVENUE' | 'EXPENSE';
}

interface IncomeStatementData {
    rows: IncomeRow[];
    total_revenue: number;
    total_cost: number;
    net_profit: number;
}

const IncomeStatementTable: React.FC = () => {
    const [data, setData] = useState<IncomeStatementData | null>(null);
    const [loading, setLoading] = useState(false);
    const { currentBook } = useBook();
    const [dates, setDates] = useState<[dayjs.Dayjs, dayjs.Dayjs]>([
        dayjs().startOf('year'),
        dayjs().endOf('year')
    ]);

    const fetchReport = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get('/reports/income-statement', {
            params: {
                book_code: currentBook.code,
                start_date: dates[0].format('YYYY-MM-DD'),
                end_date: dates[1].format('YYYY-MM-DD'),
            }
        }).then(res => {
            setData(res.data.data);
            setLoading(false);
        }).catch(err => {
            console.error(err);
            setLoading(false);
        });
    };

    useEffect(() => {
        if (currentBook) {
            fetchReport();
        }
    }, [currentBook]);

    const formatMoney = (val: number) => {
        return val.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const columns: TableProps<IncomeRow>['columns'] = [
        {
            title: '项目',
            dataIndex: 'name',
            key: 'name',
            render: (text, record) => (
                <div className="flex items-center gap-2">
                    <span className="text-slate-500 font-mono text-xs">{record.code}</span>
                    <span className={record.type === 'REVENUE' ? 'text-emerald-400 font-medium' : 'text-slate-200'}>{text}</span>
                </div>
            )
        },
        {
            title: '本期金额',
            dataIndex: 'amount',
            key: 'amount',
            align: 'right',
            render: (val, record) => (
                <span className={`font-mono ${record.type === 'REVENUE' ? 'text-emerald-400' : 'text-amber-400'}`}>
                    {record.type === 'EXPENSE' ? '-' : ''}{formatMoney(val)}
                </span>
            )
        }
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center bg-slate-800/50 p-4 rounded-xl border border-slate-700/50">
                <Space>
                    <span className="text-slate-300">查询期间:</span>
                    <DatePicker.RangePicker
                        value={dates}
                        onChange={(d) => d && setDates([d[0]!, d[1]!])}
                        allowClear={false}
                        className="bg-slate-900 border-slate-700 text-slate-200"
                    />
                    <Button type="primary" icon={<SearchOutlined />} onClick={fetchReport} loading={loading}>
                        查询
                    </Button>
                </Space>
                {data && (
                    <Space size="large">
                        <div className="text-right">
                            <div className="text-xs text-slate-500 uppercase tracking-wider">净利润 (Net Profit)</div>
                            <div className={`text-xl font-bold font-mono ${data.net_profit >= 0 ? 'text-emerald-500' : 'text-rose-500'}`}>
                                {formatMoney(data.net_profit)}
                            </div>
                        </div>
                    </Space>
                )}
            </div>

            <Spin spinning={loading}>
                {!data ? (
                    <Empty description={<span className="text-slate-500">请选择日期查询利润表</span>} />
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div className="lg:col-span-2">
                            <Table
                                dataSource={data.rows}
                                columns={columns}
                                size="middle"
                                pagination={false}
                                rowKey="code"
                                className="custom-table"
                                footer={() => (
                                    <div className="flex justify-between font-bold text-slate-200 p-2 bg-slate-800/20 rounded">
                                        <span>利润总额</span>
                                        <span className={data.net_profit >= 0 ? 'text-emerald-400' : 'text-rose-400'}>
                                            {formatMoney(data.net_profit)}
                                        </span>
                                    </div>
                                )}
                            />
                        </div>

                        <div className="space-y-4">
                            <div className="bg-slate-800/50 p-5 rounded-xl border border-slate-700/50">
                                <h4 className="text-slate-400 text-sm mb-4">损益概览</h4>
                                <div className="space-y-4">
                                    <div>
                                        <div className="flex justify-between text-xs mb-1">
                                            <span className="text-slate-500">总收入</span>
                                            <span className="text-emerald-400">{formatMoney(data.total_revenue)}</span>
                                        </div>
                                        <div className="h-1.5 w-full bg-slate-700 rounded-full overflow-hidden">
                                            <div className="h-full bg-emerald-500" style={{ width: '100%' }}></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="flex justify-between text-xs mb-1">
                                            <span className="text-slate-500">总成本费用</span>
                                            <span className="text-amber-400">{formatMoney(data.total_cost)}</span>
                                        </div>
                                        <div className="h-1.5 w-full bg-slate-700 rounded-full overflow-hidden">
                                            <div
                                                className="h-full bg-amber-500"
                                                style={{ width: `${Math.min((data.total_cost / (data.total_revenue || 1)) * 100, 100)}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                    <Divider className="border-slate-700 m-0" />
                                    <div className="pt-2">
                                        <div className="flex justify-between items-center">
                                            <span className="text-slate-300 font-bold">净利率</span>
                                            <span className={`text-lg font-mono font-bold ${data.net_profit >= 0 ? 'text-emerald-500' : 'text-rose-500'}`}>
                                                {data.total_revenue > 0 ? ((data.net_profit / data.total_revenue) * 100).toFixed(2) : 0}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-gradient-to-br from-indigo-900/40 to-slate-900/40 p-5 rounded-xl border border-indigo-500/20">
                                <Space className="text-indigo-400 mb-2">
                                    <LineChartOutlined />
                                    <span className="font-bold">分析提示</span>
                                </Space>
                                <p className="text-xs text-slate-400 leading-relaxed">
                                    当前阶段净利润为 <span className={data.net_profit >= 0 ? 'text-emerald-400' : 'text-rose-400'}>{formatMoney(data.net_profit)}</span>。
                                    请注意核对营业外收支情况，确保成本归集的准确性。
                                </p>
                            </div>
                        </div>
                    </div>
                )}
            </Spin>
        </div>
    );
};

const Divider = ({ className }: { className?: string }) => <div className={`h-px w-full ${className}`} />;

export default IncomeStatementTable;
