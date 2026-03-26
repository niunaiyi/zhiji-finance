import React, { useState, useEffect } from 'react';
import { Card, Table, Typography, Form, Select, DatePicker, Button, message, Tag } from 'antd';
import { SearchOutlined, PrinterOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';
import apiClient from '../../api/client';
import { useBook } from '../../context/BookContext';

const { Title, Text } = Typography;

const AuxiliaryLedger: React.FC = () => {
    const { currentBook } = useBook();
    const [loading, setLoading] = useState(false);
    const [data, setData] = useState<any[]>([]);
    const [auxiliaryItems, setAuxiliaryItems] = useState<any[]>([]);
    const [form] = Form.useForm();

    useEffect(() => {
        // Fetch all auxiliary items for the filter
        apiClient.get('/auxiliary-items?limit=1000').then((res: { data: { data: any[] } }) => {
            setAuxiliaryItems(res.data.data);
        });
    }, []);

    const onFinish = (values: any) => {
        if (!currentBook) return;
        setLoading(true);
        const [startDate, endDate] = values.date_range;

        apiClient.get('/reports/auxiliary-ledger', {
            params: {
                book_code: currentBook.code,
                auxiliary_item_code: values.auxiliary_item_code,
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD'),
            }
        }).then((res: { data: { data: { rows: any[] } } }) => {
            setData(res.data.data.rows);
            setLoading(false);
        }).catch(() => {
            message.error('获取报表失败');
            setLoading(false);
        });
    };

    const columns = [
        { title: '日期', dataIndex: 'date', key: 'date', width: 120 },
        { title: '凭证号', dataIndex: 'voucher_number', key: 'voucher_number', width: 100, render: (val: any) => val ? `记-${String(val).padStart(3, '0')}` : '-' },
        { title: '摘要', dataIndex: 'summary', key: 'summary' },
        { title: '对方科目', dataIndex: 'subject_name', key: 'subject_name' },
        { title: '借方', dataIndex: 'debit_amount', key: 'debit_amount', align: 'right', render: (val: number) => (val || 0) > 0 ? (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '' },
        { title: '贷方', dataIndex: 'credit_amount', key: 'credit_amount', align: 'right', render: (val: number) => (val || 0) > 0 ? (val || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) : '' },
        { title: '方向', dataIndex: 'direction', key: 'direction', align: 'center', width: 60, render: (val: string) => <Tag color={val === '借' ? 'blue' : 'orange'}>{val}</Tag> },
        { title: '余额', dataIndex: 'balance', key: 'balance', align: 'right', render: (val: number) => Math.abs(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) },
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">辅助明细账</Title>
                    <Text className="text-slate-400">按核算项目查询账务明细</Text>
                </div>
                <Button icon={<PrinterOutlined />} onClick={() => window.print()}>导出 PDF</Button>
            </div>

            <Card className="glass-panel rounded-xl">
                <Form
                    form={form}
                    layout="inline"
                    onFinish={onFinish}
                    initialValues={{
                        date_range: [dayjs().startOf('year'), dayjs()],
                    }}
                >
                    <Form.Item name="auxiliary_item_code" label="核算项目" rules={[{ required: true, message: '请选择核算项目' }]}>
                        <Select
                            showSearch
                            placeholder="搜索编码或名称"
                            optionFilterProp="children"
                            style={{ width: 250 }}
                            options={auxiliaryItems.map(item => ({
                                label: `${item.code} ${item.name} (${item.category_code})`,
                                value: item.code
                            }))}
                        />
                    </Form.Item>
                    <Form.Item name="date_range" label="日期范围" rules={[{ required: true }]}>
                        <DatePicker.RangePicker />
                    </Form.Item>
                    <Form.Item>
                        <Button type="primary" htmlType="submit" icon={<SearchOutlined />} loading={loading}>
                            查询
                        </Button>
                    </Form.Item>
                </Form>
            </Card>

            <Card className="glass-panel rounded-xl p-0 overflow-hidden">
                <Table
                    dataSource={data}
                    columns={columns as any}
                    loading={loading}
                    pagination={false}
                    rowKey={(record, index) => `${record.date}-${record.voucher_number || 'init'}-${index}`}
                    size="middle"
                    className="custom-table"
                />
            </Card>
        </div>
    );
};

export default AuxiliaryLedger;
