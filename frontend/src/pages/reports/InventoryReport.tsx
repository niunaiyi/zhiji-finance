import React, { useEffect, useState } from 'react';
import { Card, Table, Typography } from 'antd';
import apiClient from '../../api/client';
import { useBook } from '../../context/BookContext';

const { Title, Text } = Typography;

const InventoryReport: React.FC = () => {
    const { currentBook } = useBook();
    const [data, setData] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);

    const fetchBalance = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get(`/inventory/balance?book_code=${currentBook.code}`).then(res => {
            setData(res.data.data);
            setLoading(false);
        }).catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchBalance();
    }, [currentBook]);

    const columns = [
        { title: 'SKU', dataIndex: 'sku', key: 'sku' },
        { title: '品名', dataIndex: 'name', key: 'name' },
        { title: '单位', dataIndex: 'unit', key: 'unit' },
        { title: '数量', dataIndex: 'quantity', key: 'quantity', align: 'right' as const, render: (val: number) => val },
        { title: '金额', dataIndex: 'amount', key: 'amount', align: 'right' as const, render: (val: number) => (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) },
        {
            title: '期末金额',
            key: 'total',
            align: 'right' as const,
            render: (_: any, record: any) => `¥${(record.current_quantity * record.current_average_cost).toFixed(2)}`
        },
    ];

    return (
        <div className="space-y-6">
            <div>
                <Title level={2} className="!text-slate-200 !mb-1">库存余额表</Title>
                <Text className="text-slate-400">查看当前库存结存及平均成本</Text>
            </div>

            <Card className="glass-panel">
                <Table dataSource={data} columns={columns} loading={loading} rowKey="id" pagination={false} />
            </Card>
        </div>
    );
};

export default InventoryReport;
