import React, { useEffect, useState } from 'react';
import { DatePicker, Button, Space, Empty, Spin, Tag } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import apiClient from '../api/client';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';

interface BalanceItem {
    code: string;
    name: string;
    balance: number;
}

interface BalanceSheetData {
    assets: BalanceItem[];
    liabilities: BalanceItem[];
    equity: BalanceItem[];
    total_assets: number;
    total_liabilities: number;
    total_equity: number;
    total_liabilities_equity: number;
}

const BalanceSheetTable: React.FC = () => {
    const [data, setData] = useState<BalanceSheetData | null>(null);
    const [loading, setLoading] = useState(false);
    const { currentBook } = useBook();
    const [date, setDate] = useState<dayjs.Dayjs>(dayjs().endOf('year'));

    const fetchReport = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get('/reports/balance-sheet', {
            params: {
                book_code: currentBook.code,
                date: date.format('YYYY-MM-DD'),
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

    const formatMoney = (amount: number) => {
        return amount.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const renderSection = (title: string, items: BalanceItem[], total: number, colorClass: string) => (
        <div className="flex flex-col h-full bg-slate-800/30 rounded-lg p-4 border border-slate-700/50">
            <h3 className={`text-lg font-bold mb-4 ${colorClass} border-b border-slate-700/50 pb-2`}>{title}</h3>
            {items.length === 0 ? (
                <Empty description={<span className="text-slate-500">暂无数据</span>} image={Empty.PRESENTED_IMAGE_SIMPLE} />
            ) : (
                <div className="flex-1 space-y-2">
                    {items.map(item => (
                        <div key={item.code} className="flex justify-between items-center text-sm py-1 border-b border-slate-700/30 last:border-0 hover:bg-slate-700/20 px-2 rounded transition-colors">
                            <span className="text-slate-300">
                                <span className="text-slate-500 mr-2 font-mono text-xs">{item.code}</span>
                                {item.name}
                            </span>
                            <span className="font-mono text-slate-200">{formatMoney(item.balance)}</span>
                        </div>
                    ))}
                </div>
            )}
            <div className="mt-4 pt-3 border-t border-slate-600 border-dashed flex justify-between items-center">
                <span className="font-bold text-slate-300">合计</span>
                <span className={`font-bold font-mono text-lg ${colorClass}`}>{formatMoney(total)}</span>
            </div>
        </div>
    );

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center bg-slate-800/50 p-4 rounded-xl border border-slate-700/50">
                <Space>
                    <span className="text-slate-300">报表日期:</span>
                    <DatePicker
                        value={date}
                        onChange={(d) => d && setDate(d)}
                        allowClear={false}
                        className="bg-slate-900 border-slate-700 text-slate-200"
                    />
                    <Button type="primary" icon={<SearchOutlined />} onClick={fetchReport} loading={loading}>
                        查询
                    </Button>
                </Space>
                {data && (
                    <Tag color={data.total_assets === data.total_liabilities_equity ? 'success' : 'error'} className="px-3 py-1 text-sm">
                        {data.total_assets === data.total_liabilities_equity ? '资产负债表平衡' : '警告：资产负债表不平衡'}
                    </Tag>
                )}
            </div>

            <Spin spinning={loading}>
                {!data ? (
                    <Empty description={<span className="text-slate-500">请选择日期查询报表</span>} />
                ) : (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* 左侧：资产 */}
                        <div>
                            {renderSection('资产 (Assets)', data.assets, data.total_assets, 'text-emerald-400')}
                        </div>

                        {/* 右侧：负债及所有者权益 */}
                        <div className="space-y-6">
                            {renderSection('负债 (Liabilities)', data.liabilities, data.total_liabilities, 'text-amber-400')}
                            {renderSection('所有者权益 (Equity)', data.equity, data.total_equity, 'text-blue-400')}

                            <div className="bg-slate-800/50 rounded-lg p-4 border border-slate-700 flex justify-between items-center shadow-lg shadow-black/20">
                                <span className="font-bold text-slate-200 text-lg">负债及所有者权益总计</span>
                                <span className="font-bold font-mono text-xl text-purple-400">{formatMoney(data.total_liabilities_equity)}</span>
                            </div>
                        </div>
                    </div>
                )}
            </Spin>
        </div>
    );
};

export default BalanceSheetTable;
