import React, { useEffect, useState } from 'react';
import { Table, Card, DatePicker, Button, Space, Tag, Statistic, Modal } from 'antd';
import type { TableProps } from 'antd';
import { SearchOutlined, FolderOpenOutlined } from '@ant-design/icons';
import apiClient from '../api/client';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';
import LedgerTable from './LedgerTable';

const { RangePicker } = DatePicker;

interface TrialBalanceData {
    subject_code: string;
    subject_name: string;
    balance_direction: string;
    opening_balance: number;
    period_debit: number;
    period_credit: number;
    closing_balance: number;
}

const TrialBalanceTable: React.FC = () => {
    const [data, setData] = useState<TrialBalanceData[]>([]);
    const [loading, setLoading] = useState(false);
    const [drillSubject, setDrillSubject] = useState<{ code: string, name: string } | null>(null);
    const { currentBook } = useBook();
    const [dateRange, setDateRange] = useState<[dayjs.Dayjs, dayjs.Dayjs]>([
        dayjs().startOf('year'),
        dayjs().endOf('year')
    ]);

    const fetchReport = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get('/reports/trial-balance', {
            params: {
                book_code: currentBook.code,
                start_date: dateRange[0].format('YYYY-MM-DD'),
                end_date: dateRange[1].format('YYYY-MM-DD'),
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
        if (amount === 0) return '-';
        return amount.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    const columns: TableProps<TrialBalanceData>['columns'] = [
        {
            title: '科目编码',
            dataIndex: 'subject_code',
            key: 'subject_code',
            width: 120,
            render: (text) => <span className="font-mono text-slate-300">{text}</span>
        },
        {
            title: '科目名称',
            dataIndex: 'subject_name',
            key: 'subject_name',
            width: 200,
            render: (text) => <span className="font-medium text-slate-200">{text}</span>
        },
        {
            title: '方向',
            dataIndex: 'balance_direction',
            key: 'balance_direction',
            width: 80,
            render: (text) => text === '借' ? <Tag color="green">借</Tag> : <Tag color="orange">贷</Tag>
        },
        {
            title: '期初余额',
            dataIndex: 'opening_balance',
            key: 'opening_balance',
            align: 'right',
            render: (val) => <span className="font-mono text-slate-300">{formatMoney(val)}</span>
        },
        {
            title: '本期发生额',
            children: [
                {
                    title: '借方',
                    dataIndex: 'period_debit',
                    key: 'period_debit',
                    align: 'right',
                    render: (val) => <span className="font-mono text-emerald-400">{formatMoney(val)}</span>
                },
                {
                    title: '贷方',
                    dataIndex: 'period_credit',
                    key: 'period_credit',
                    align: 'right',
                    render: (val) => <span className="font-mono text-amber-400">{formatMoney(val)}</span>
                }
            ]
        },
        {
            title: '期末余额',
            dataIndex: 'closing_balance',
            key: 'closing_balance',
            align: 'right',
            render: (val) => <span className="font-bold font-mono text-blue-400">{formatMoney(val)}</span>
        },
        {
            title: '明细',
            key: 'action',
            width: 80,
            align: 'center',
            render: (_, record) => (
                <Button
                    type="text"
                    icon={<FolderOpenOutlined />}
                    className="text-amber-500"
                    onClick={(e) => {
                        e.stopPropagation();
                        setDrillSubject({ code: record.subject_code, name: record.subject_name });
                    }}
                />
            )
        }
    ];

    const totalDebit = data.reduce((sum, item) => sum + item.period_debit, 0);
    const totalCredit = data.reduce((sum, item) => sum + item.period_credit, 0);

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <Space>
                    <RangePicker
                        value={dateRange}
                        onChange={(dates) => {
                            if (dates && dates[0] && dates[1]) {
                                setDateRange([dates[0], dates[1]]);
                            }
                        }}
                        className="bg-slate-800 border-slate-700 text-slate-200"
                    />
                    <Button type="primary" icon={<SearchOutlined />} onClick={fetchReport} className="shadow-md shadow-amber-500/20">
                        查询
                    </Button>
                </Space>
                <div className="flex gap-6 px-6 py-2 bg-slate-800/50 rounded-lg border border-slate-700/50">
                    <Statistic
                        title={<span className="text-slate-400 text-xs">本期借方总计</span>}
                        value={totalDebit}
                        precision={2}
                        valueStyle={{ color: '#34D399', fontFamily: 'IBM Plex Sans', fontSize: '18px', fontWeight: 'bold' }}
                        prefix={<span className="text-xs mr-1">¥</span>}
                    />
                    <div className="w-px bg-slate-700/50 h-full"></div>
                    <Statistic
                        title={<span className="text-slate-400 text-xs">本期贷方总计</span>}
                        value={totalCredit}
                        precision={2}
                        valueStyle={{ color: '#FBBF24', fontFamily: 'IBM Plex Sans', fontSize: '18px', fontWeight: 'bold' }}
                        prefix={<span className="text-xs mr-1">¥</span>}
                    />
                </div>
            </div>

            <Card variant="borderless" className="glass-panel rounded-xl overflow-hidden p-0" styles={{ body: { padding: 0 } }}>
                <Table<TrialBalanceData>
                    columns={columns}
                    dataSource={data}
                    loading={loading}
                    rowKey="subject_code"
                    pagination={false}
                    size="small"
                    scroll={{ y: 600 }}
                    bordered
                    className="report-table"
                    onRow={(record) => ({
                        onClick: () => setDrillSubject({ code: record.subject_code, name: record.subject_name }),
                        className: 'cursor-pointer hover:bg-slate-800/20 transition-colors'
                    })}
                />
            </Card>

            <Modal
                title={`${drillSubject?.code} ${drillSubject?.name} - 明细账`}
                open={!!drillSubject}
                onCancel={() => setDrillSubject(null)}
                footer={null}
                width={1200}
                destroyOnClose
            >
                {drillSubject && (
                    <LedgerTable
                        subjectCode={drillSubject.code}
                        startDate={dateRange[0].format('YYYY-MM-DD')}
                        endDate={dateRange[1].format('YYYY-MM-DD')}
                    />
                )}
            </Modal>
        </div>
    );
};

export default TrialBalanceTable;
