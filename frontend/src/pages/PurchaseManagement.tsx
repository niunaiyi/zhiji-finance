import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Modal, Form, Input, InputNumber, message, Tag, DatePicker } from 'antd';
import { PlusOutlined, FileSearchOutlined, ExportOutlined, AuditOutlined } from '@ant-design/icons';
import { purchaseApi, type PurchaseBill } from '../api/purchase';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const PurchaseManagement: React.FC = () => {
    const { currentBook } = useBook();
    const [bills, setBills] = useState<PurchaseBill[]>([]);
    const [loading, setLoading] = useState(false);
    const [modalOpen, setModalOpen] = useState(false);
    const [form] = Form.useForm();

    const fetchBills = () => {
        setLoading(true);
        purchaseApi.listBills({ limit: 50 }).then(res => {
            setBills(res.data.data || res.data);
            setLoading(false);
        }).catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchBills();
    }, []);

    const handleCreateBill = () => {
        form.validateFields().then(values => {
            const payload = {
                ...values,
                book_code: currentBook?.code,
                bill_date: values.bill_date.format('YYYY-MM-DD'),
            };
            purchaseApi.createBill(payload).then(() => {
                message.success('采购单创建成功');
                setModalOpen(false);
                fetchBills();
            }).catch(err => {
                message.error(err.response?.data?.message || '创建失败');
            });
        });
    };

    const handlePost = (id: number) => {
        purchaseApi.postBill(id).then(() => {
            message.success('已生成凭证并入账');
            fetchBills();
        }).catch(err => {
            message.error(err.response?.data?.message || '过账失败');
        });
    };

    const columns = [
        { title: '单据编号', dataIndex: 'bill_no', key: 'bill_no' },
        { title: '日期', dataIndex: 'bill_date', key: 'bill_date' },
        { title: '供应商', dataIndex: 'vendor_name', key: 'vendor_name' },
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
            render: (_: any, record: PurchaseBill) => (
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
                    <Title level={2} className="!text-slate-200 !mb-1">采购管理</Title>
                    <p className="text-slate-400">采购订单与进货发票管理</p>
                </div>
                <Space>
                    <Button icon={<ExportOutlined />}>导出</Button>
                    <Button type="primary" icon={<PlusOutlined />} onClick={() => { form.resetFields(); setModalOpen(true); }}>新增采购单</Button>
                </Space>
            </div>

            <Card className="glass-panel">
                <Table dataSource={bills} columns={columns} loading={loading} rowKey="id" />
            </Card>

            <Modal title="新增采购单" open={modalOpen} onOk={handleCreateBill} onCancel={() => setModalOpen(false)} width={600}>
                <Form form={form} layout="vertical">
                    <Form.Item name="vendor_name" label="供应商名称" rules={[{ required: true }]}><Input placeholder="输入供应商名称" /></Form.Item>
                    <Form.Item name="bill_date" label="单据日期" rules={[{ required: true }]} initialValue={dayjs()}><DatePicker style={{ width: '100%' }} /></Form.Item>
                    <Form.Item name="total_amount" label="总金额" rules={[{ required: true }]}><InputNumber style={{ width: '100%' }} min={0} precision={2} /></Form.Item>
                    <Form.Item name="remark" label="备注"><Input.TextArea /></Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default PurchaseManagement;
