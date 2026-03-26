import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Modal, Form, Input, InputNumber, message, Tag, DatePicker } from 'antd';
import { PlusOutlined, FileSearchOutlined, AuditOutlined } from '@ant-design/icons';
import { salesApi, type SalesInvoice } from '../api/sales';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const SalesManagement: React.FC = () => {
    const { currentBook } = useBook();
    const [invoices, setInvoices] = useState<SalesInvoice[]>([]);
    const [loading, setLoading] = useState(false);
    const [modalOpen, setModalOpen] = useState(false);
    const [form] = Form.useForm();

    const fetchInvoices = () => {
        setLoading(true);
        salesApi.listInvoices({ limit: 50 }).then(res => {
            setInvoices(res.data.data || res.data);
            setLoading(false);
        }).catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchInvoices();
    }, []);

    const handleCreateInvoice = () => {
        form.validateFields().then(values => {
            const payload = {
                ...values,
                book_code: currentBook?.code,
                invoice_date: values.invoice_date.format('YYYY-MM-DD'),
            };
            salesApi.createInvoice(payload).then(() => {
                message.success('销售单创建成功');
                setModalOpen(false);
                fetchInvoices();
            }).catch(err => {
                message.error(err.response?.data?.message || '创建失败');
            });
        });
    };

    const handlePost = (id: number) => {
        salesApi.postInvoice(id).then(() => {
            message.success('已生成凭证并入账');
            fetchInvoices();
        }).catch(err => {
            message.error(err.response?.data?.message || '过账失败');
        });
    };

    const columns = [
        { title: '单据编号', dataIndex: 'invoice_no', key: 'invoice_no' },
        { title: '日期', dataIndex: 'invoice_date', key: 'invoice_date' },
        { title: '客户', dataIndex: 'customer_name', key: 'customer_name' },
        { 
            title: '总金额', 
            dataIndex: 'total_amount', 
            key: 'total_amount',
            align: 'right' as const,
            render: (val: number) => <Text strong>¥{Number(val).toLocaleString()}</Text>
        },
        { 
            title: '状态', 
            dataIndex: 'status', 
            key: 'status',
            render: (status: string) => (
                <Tag color={status === 'posted' ? 'blue' : 'orange'}>
                    {status === 'posted' ? '已入账' : '草稿'}
                </Tag>
            )
        },
        {
            title: '操作',
            key: 'action',
            render: (_: any, record: SalesInvoice) => (
                <Space>
                    <Button size="small" icon={<FileSearchOutlined />}>详情</Button>
                    {record.status === 'draft' && (
                        <Button size="small" type="primary" icon={<AuditOutlined />} onClick={() => handlePost(record.id)}>过账</Button>
                    )}
                </Space>
            )
        }
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">销售管理</Title>
                    <p className="text-slate-400">销售订单与发票管理</p>
                </div>
                <Space>
                    <Button type="primary" icon={<PlusOutlined />} onClick={() => { form.resetFields(); setModalOpen(true); }}>新增销售单</Button>
                </Space>
            </div>

            <Card className="glass-panel">
                <Table dataSource={invoices} columns={columns} loading={loading} rowKey="id" />
            </Card>

            <Modal title="新增销售单" open={modalOpen} onOk={handleCreateInvoice} onCancel={() => setModalOpen(false)} width={600}>
                <Form form={form} layout="vertical">
                    <Form.Item name="customer_name" label="客户名称" rules={[{ required: true }]}><Input placeholder="输入客户名称" /></Form.Item>
                    <Form.Item name="invoice_date" label="单据日期" rules={[{ required: true }]} initialValue={dayjs()}><DatePicker style={{ width: '100%' }} /></Form.Item>
                    <Form.Item name="total_amount" label="总金额" rules={[{ required: true }]}><InputNumber style={{ width: '100%' }} min={0} precision={2} /></Form.Item>
                    <Form.Item name="remark" label="备注"><Input.TextArea /></Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default SalesManagement;
