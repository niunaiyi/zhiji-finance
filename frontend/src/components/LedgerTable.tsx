import React, { useEffect, useState } from 'react';
import { Table, Button } from 'antd';
import type { TableProps } from 'antd';
import apiClient from '../api/client';
import { useBook } from '../context/BookContext';
import { PrinterOutlined } from '@ant-design/icons';

interface LedgerRow {
    date: string;
    voucher_number: number | null;
    summary: string;
    debit_amount: number;
    credit_amount: number;
    balance: number;
    direction: string;
}

interface LedgerTableProps {
    subjectCode: string;
    startDate: string;
    endDate: string;
}

const LedgerTable: React.FC<LedgerTableProps> = ({ subjectCode, startDate, endDate }) => {
    const [data, setData] = useState<LedgerRow[]>([]);
    const [loading, setLoading] = useState(false);
    const { currentBook } = useBook();

    const fetchLedger = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get('/reports/ledger', {
            params: {
                book_code: currentBook.code,
                subject_code: subjectCode,
                start_date: startDate,
                end_date: endDate
            }
        }).then(res => {
            setData(res.data.data.rows);
            setLoading(false);
        }).catch(err => {
            console.error(err);
            setLoading(false);
        });
    };

    useEffect(() => {
        fetchLedger();
    }, [subjectCode, startDate, endDate]);

    const formatMoney = (amount: number) => {
        if (amount === 0) return '-';
        return amount.toLocaleString('zh-CN', { minimumFractionDigits: 2 });
    };

    const columns: TableProps<LedgerRow>['columns'] = [
        { title: '日期', dataIndex: 'date', key: 'date', width: 110 },
        { title: '凭证号', dataIndex: 'voucher_number', key: 'voucher_number', width: 90, render: (val) => val ? `记-${String(val).padStart(3, '0')}` : '' },
        { title: '摘要', dataIndex: 'summary', key: 'summary' },
        { title: '借方发生额', dataIndex: 'debit_amount', key: 'debit_amount', align: 'right', render: (val) => <span className="text-emerald-500">{formatMoney(val)}</span> },
        { title: '贷方发生额', dataIndex: 'credit_amount', key: 'credit_amount', align: 'right', render: (val) => <span className="text-amber-500">{formatMoney(val)}</span> },
        { title: '方向', dataIndex: 'direction', key: 'direction', width: 60, align: 'center' },
        { title: '余额', dataIndex: 'balance', key: 'balance', align: 'right', render: (val) => <span className="font-bold">{formatMoney(val)}</span> },
    ];

    return (
        <div className="space-y-4">
            <div className="flex justify-end p-2">
                <Button icon={<PrinterOutlined />} onClick={() => window.print()}>打印明细账</Button>
            </div>
            <Table
                columns={columns}
                dataSource={data}
                loading={loading}
                rowKey={(record, index) => `${record.date}-${index}`}
                pagination={false}
                size="small"
                bordered
                className="ledger-table-content"
                scroll={{ y: 500 }}
            />
        </div>
    );
};

export default LedgerTable;
