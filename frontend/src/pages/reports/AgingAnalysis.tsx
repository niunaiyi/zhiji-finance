import React, { useEffect, useState } from 'react';
import { Card, Table, Space, Button, Typography, Radio, DatePicker } from 'antd';
import { DownloadOutlined, PrinterOutlined } from '@ant-design/icons';
import apiClient from '../../api/client';
import { useBook } from '../../context/BookContext';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

interface AgingData {
    name: string;
    code: string;
    total: number;
    bucket_1_30: number;
    bucket_31_60: number;
    bucket_61_90: number;
    bucket_91_plus: number;
}

const AgingAnalysis: React.FC = () => {
    const { currentBook } = useBook();
    const [type, setType] = useState<'AR' | 'AP'>('AR');
    const [date, setDate] = useState<dayjs.Dayjs>(dayjs());
    const [data, setData] = useState<AgingData[]>([]);
    const [loading, setLoading] = useState(false);

    const fetchReport = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get(`/reports/aging-analysis?book_code=${currentBook.code}&type=${type}&date=${date.format('YYYY-MM-DD')}`)
            .then(res => {
                setData(res.data.data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchReport();
    }, [currentBook, type, date]);

    const columns = [
        { title: type === 'AR' ? '客户名称' : '供应商名称', dataIndex: 'name', key: 'name' },
        { title: '科目编码', dataIndex: 'code', key: 'code', width: 100 },
        { title: '总余额', dataIndex: 'total', key: 'total', align: 'right' as const, render: (val: number) => (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) },
        { title: '1-30天', dataIndex: 'bucket_1_30', key: 'bucket_1_30', align: 'right' as const, render: (val: number) => (val || 0).toLocaleString() },
        { title: '31-60天', dataIndex: 'bucket_31_60', key: 'bucket_31_60', align: 'right' as const, render: (val: number) => (val || 0).toLocaleString() },
        { title: '61-90天', dataIndex: 'bucket_61_90', key: 'bucket_61_90', align: 'right' as const, render: (val: number) => (val || 0).toLocaleString() },
        { title: '90天以上', dataIndex: 'bucket_91_plus', key: 'bucket_91_plus', align: 'right' as const, render: (val: number) => (val || 0).toLocaleString() },
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">账龄分析</Title>
                    <Text className="text-slate-400">分析应收/应付账款的欠款时长</Text>
                </div>
                <Space>
                    <Radio.Group value={type} onChange={e => setType(e.target.value)} buttonStyle="solid">
                        <Radio.Button value="AR">应收账款</Radio.Button>
                        <Radio.Button value="AP">应付账款</Radio.Button>
                    </Radio.Group>
                    <DatePicker value={date} onChange={val => val && setDate(val)} />
                    <Button icon={<PrinterOutlined />} onClick={() => window.print()}>打印</Button>
                    <Button type="primary" icon={<DownloadOutlined />}>导出</Button>
                </Space>
            </div>

            <Card className="glass-panel">
                <Table
                    dataSource={data}
                    columns={columns}
                    loading={loading}
                    rowKey="code"
                    pagination={false}
                    summary={(pageData) => {
                        let total = 0;
                        let b1 = 0; let b2 = 0; let b3 = 0; let b4 = 0;
                        pageData.forEach(({ total: t, bucket_1_30: v1, bucket_31_60: v2, bucket_61_90: v3, bucket_91_plus: v4 }) => {
                            total += t; b1 += v1; b2 += v2; b3 += v3; b4 += v4;
                        });
                        return (
                            <Table.Summary fixed>
                                <Table.Summary.Row className="bg-slate-800/50 font-bold">
                                    <Table.Summary.Cell index={0} colSpan={2}>合计</Table.Summary.Cell>
                                    <Table.Summary.Cell index={1} align="right">{(total || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}</Table.Summary.Cell>
                                    <Table.Summary.Cell index={2} align="right">{(b1 || 0).toLocaleString()}</Table.Summary.Cell>
                                    <Table.Summary.Cell index={3} align="right">{(b2 || 0).toLocaleString()}</Table.Summary.Cell>
                                    <Table.Summary.Cell index={4} align="right">{(b3 || 0).toLocaleString()}</Table.Summary.Cell>
                                    <Table.Summary.Cell index={5} align="right">{(b4 || 0).toLocaleString()}</Table.Summary.Cell>
                                </Table.Summary.Row>
                            </Table.Summary>
                        );
                    }}
                />
            </Card>
        </div>
    );
};

export default AgingAnalysis;
