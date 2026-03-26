import React, { useState, useEffect, useCallback } from 'react';
import {
    Card, Table, Button, Tag, Modal, Form, Input, InputNumber,
    DatePicker, Select, message, Tabs, Typography, Statistic, Row, Col
} from 'antd';
import type { TableProps } from 'antd';
import { PlusOutlined, LinkOutlined } from '@ant-design/icons';
import { arApi } from '../api/ar';
import type { ArBill, ArReceipt } from '../api/ar';
import { periodsApi } from '../api/periods';
import type { Period } from '../types/period';

const { Title, Text } = Typography;

const STATUS_COLORS: Record<string, string> = {
    open: 'blue', partial: 'orange', settled: 'green', voided: 'red',
};
const STATUS_LABELS: Record<string, string> = {
    open: '未结清', partial: '部分结清', settled: '已结清', voided: '已作废',
};

const AccountsReceivable: React.FC = () => {
    const [bills, setBills] = useState<ArBill[]>([]);
    const [receipts, setReceipts] = useState<ArReceipt[]>([]);
    const [periods, setPeriods] = useState<Period[]>([]);
    const [loadingBills, setLoadingBills] = useState(false);
    const [loadingReceipts, setLoadingReceipts] = useState(false);

    // Settlement modal state
    const [settleOpen, setSettleOpen] = useState(false);
    const [settlingBill, setSettlingBill] = useState<ArBill | null>(null);
    const [settleForm] = Form.useForm();

    // Create modals
    const [billModalOpen, setBillModalOpen] = useState(false);
    const [receiptModalOpen, setReceiptModalOpen] = useState(false);
    const [billForm] = Form.useForm();
    const [receiptForm] = Form.useForm();

    const fetchBills = useCallback(async (params?: any) => {
        setLoadingBills(true);
        try {
            const resp = await arApi.listBills(params);
            setBills(resp.data?.data || []);
        } catch { message.error('加载应收单据失败'); }
        finally { setLoadingBills(false); }
    }, []);

    const fetchReceipts = useCallback(async (params?: any) => {
        setLoadingReceipts(true);
        try {
            const resp = await arApi.listReceipts(params);
            setReceipts(resp.data?.data || []);
        } catch { message.error('加载收款单失败'); }
        finally { setLoadingReceipts(false); }
    }, []);

    const fetchMeta = useCallback(async () => {
        const [pResp] = await Promise.allSettled([
            periodsApi.list({ status: 'open' }),
        ]);
        if (pResp.status === 'fulfilled') setPeriods(pResp.value.data || []);
    }, []);

    useEffect(() => {
        fetchBills();
        fetchReceipts();
        fetchMeta();
    }, [fetchBills, fetchReceipts, fetchMeta]);

    const handleCreateBill = async () => {
        try {
            const v = await billForm.validateFields();
            await arApi.createBill({
                ...v,
                bill_date: v.bill_date.format('YYYY-MM-DD'),
                amount: Number(v.amount),
            });
            message.success('应收单据创建成功');
            setBillModalOpen(false);
            billForm.resetFields();
            fetchBills();
        } catch (err: any) {
            if (err?.response?.data?.message) message.error(err.response.data.message);
        }
    };

    const handleCreateReceipt = async () => {
        try {
            const v = await receiptForm.validateFields();
            await arApi.createReceipt({
                ...v,
                receipt_date: v.receipt_date.format('YYYY-MM-DD'),
                amount: Number(v.amount),
            });
            message.success('收款单创建成功');
            setReceiptModalOpen(false);
            receiptForm.resetFields();
            fetchReceipts();
        } catch (err: any) {
            if (err?.response?.data?.message) message.error(err.response.data.message);
        }
    };

    const handleSettle = async () => {
        try {
            const v = await settleForm.validateFields();
            await arApi.settle(settlingBill!.id, v.ar_receipt_id, Number(v.amount));
            message.success('核销成功');
            setSettleOpen(false);
            settleForm.resetFields();
            fetchBills();
            fetchReceipts();
        } catch (err: any) {
            if (err?.response?.data?.message) message.error(err.response.data.message);
            else message.error('核销失败');
        }
    };

    const openSettle = (bill: ArBill) => {
        setSettlingBill(bill);
        settleForm.setFieldsValue({ amount: bill.balance });
        setSettleOpen(true);
    };

    const billColumns: TableProps<ArBill>['columns'] = [
        { title: '单据号', dataIndex: 'bill_no', key: 'bill_no', width: 140 },
        { title: '日期', dataIndex: 'bill_date', key: 'bill_date', width: 110 },
        { title: '客户', key: 'customer', render: (_, r) => r.customer?.name || `#${r.customer_id}` },
        {
            title: '金额', dataIndex: 'amount', key: 'amount', align: 'right', width: 120,
            render: v => <span className="font-mono">¥{parseFloat(v).toFixed(2)}</span>,
        },
        {
            title: '已核销', dataIndex: 'settled_amount', key: 'settled_amount', align: 'right', width: 110,
            render: v => <span className="font-mono text-emerald-400">¥{parseFloat(v).toFixed(2)}</span>,
        },
        {
            title: '余额', dataIndex: 'balance', key: 'balance', align: 'right', width: 110,
            render: v => <span className={`font-mono ${parseFloat(v) > 0 ? 'text-amber-400' : 'text-slate-500'}`}>¥{parseFloat(v).toFixed(2)}</span>,
        },
        {
            title: '状态', dataIndex: 'status', key: 'status', width: 90,
            render: s => <Tag color={STATUS_COLORS[s]}>{STATUS_LABELS[s]}</Tag>,
        },
        {
            title: '操作', key: 'action', width: 100,
            render: (_, record) => record.status !== 'settled' && record.status !== 'voided' ? (
                <Button size="small" type="primary" icon={<LinkOutlined />} onClick={() => openSettle(record)}>
                    核销
                </Button>
            ) : null,
        },
    ];

    const receiptColumns: TableProps<ArReceipt>['columns'] = [
        { title: '收款单号', dataIndex: 'receipt_no', key: 'receipt_no', width: 140 },
        { title: '日期', dataIndex: 'receipt_date', key: 'receipt_date', width: 110 },
        { title: '客户', key: 'customer', render: (_, r) => r.customer?.name || `#${r.customer_id}` },
        {
            title: '金额', dataIndex: 'amount', key: 'amount', align: 'right', width: 120,
            render: v => <span className="font-mono">¥{parseFloat(v).toFixed(2)}</span>,
        },
        {
            title: '已核销', dataIndex: 'settled_amount', key: 'settled_amount', align: 'right', width: 110,
            render: v => <span className="font-mono text-emerald-400">¥{parseFloat(v).toFixed(2)}</span>,
        },
        {
            title: '余额', dataIndex: 'balance', key: 'balance', align: 'right', width: 110,
            render: v => <span className={`font-mono ${parseFloat(v) > 0 ? 'text-amber-400' : 'text-slate-500'}`}>¥{parseFloat(v).toFixed(2)}</span>,
        },
        {
            title: '状态', dataIndex: 'status', key: 'status', width: 90,
            render: s => <Tag color={STATUS_COLORS[s]}>{STATUS_LABELS[s]}</Tag>,
        },
    ];

    const openBillTotal = bills.filter(b => b.status !== 'settled').reduce((s, b) => s + parseFloat(b.balance), 0);
    const openReceiptTotal = receipts.filter(r => r.status !== 'settled').reduce((s, r) => s + parseFloat(r.balance), 0);

    const periodOptions = periods.map(p => ({
        value: p.id,
        label: `${p.fiscal_year}-${String(p.period_number).padStart(2, '0')}`,
    }));

    const openReceiptOptions = receipts
        .filter(r => r.status !== 'settled')
        .map(r => ({
            value: r.id,
            label: `${r.receipt_no} (余额: ¥${parseFloat(r.balance).toFixed(2)})`,
        }));

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} style={{ color: '#e2e8f0', margin: 0 }}>应收管理</Title>
                    <Text type="secondary">管理应收单据、收款单及核销操作</Text>
                </div>
            </div>

            <Row gutter={16}>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="未结余额" value={openBillTotal.toFixed(2)} prefix="¥"
                            valueStyle={{ color: '#d48806' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="未使用收款" value={openReceiptTotal.toFixed(2)} prefix="¥"
                            valueStyle={{ color: '#3f8600' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="应收单总数" value={bills.length} />
                    </Card>
                </Col>
            </Row>

            <Tabs
                items={[
                    {
                        key: 'bills',
                        label: `应收单据 (${bills.length})`,
                        children: (
                            <Card bordered={false} className="glass-panel rounded-xl"
                                extra={
                                    <Button type="primary" icon={<PlusOutlined />} onClick={() => setBillModalOpen(true)}>
                                        新建应收单
                                    </Button>
                                }
                            >
                                <Table<ArBill>
                                    columns={billColumns}
                                    dataSource={bills}
                                    loading={loadingBills}
                                    rowKey="id"
                                    pagination={{ pageSize: 15 }}
                                />
                            </Card>
                        ),
                    },
                    {
                        key: 'receipts',
                        label: `收款单 (${receipts.length})`,
                        children: (
                            <Card bordered={false} className="glass-panel rounded-xl"
                                extra={
                                    <Button type="primary" icon={<PlusOutlined />} onClick={() => setReceiptModalOpen(true)}>
                                        新建收款单
                                    </Button>
                                }
                            >
                                <Table<ArReceipt>
                                    columns={receiptColumns}
                                    dataSource={receipts}
                                    loading={loadingReceipts}
                                    rowKey="id"
                                    pagination={{ pageSize: 15 }}
                                />
                            </Card>
                        ),
                    },
                ]}
            />

            {/* Create Bill Modal */}
            <Modal title="新建应收单据" open={billModalOpen} onOk={handleCreateBill}
                onCancel={() => { setBillModalOpen(false); billForm.resetFields(); }} destroyOnClose>
                <Form form={billForm} layout="vertical">
                    <Form.Item name="bill_no" label="单据号" rules={[{ required: true }]}>
                        <Input placeholder="AR-2024-001" />
                    </Form.Item>
                    <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
                        <Select options={periodOptions} placeholder="选择期间" />
                    </Form.Item>
                    <Form.Item name="customer_id" label="客户 ID" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} placeholder="客户辅助核算 ID" />
                    </Form.Item>
                    <Form.Item name="bill_date" label="单据日期" rules={[{ required: true }]}>
                        <DatePicker style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item name="amount" label="金额" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} min={0.01} precision={2} prefix="¥" />
                    </Form.Item>
                </Form>
            </Modal>

            {/* Create Receipt Modal */}
            <Modal title="新建收款单" open={receiptModalOpen} onOk={handleCreateReceipt}
                onCancel={() => { setReceiptModalOpen(false); receiptForm.resetFields(); }} destroyOnClose>
                <Form form={receiptForm} layout="vertical">
                    <Form.Item name="receipt_no" label="收款单号" rules={[{ required: true }]}>
                        <Input placeholder="RCP-2024-001" />
                    </Form.Item>
                    <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
                        <Select options={periodOptions} placeholder="选择期间" />
                    </Form.Item>
                    <Form.Item name="customer_id" label="客户 ID" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} placeholder="客户辅助核算 ID" />
                    </Form.Item>
                    <Form.Item name="receipt_date" label="收款日期" rules={[{ required: true }]}>
                        <DatePicker style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item name="amount" label="金额" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} min={0.01} precision={2} prefix="¥" />
                    </Form.Item>
                </Form>
            </Modal>

            {/* Settlement Modal */}
            <Modal
                title={`核销应收单 — ${settlingBill?.bill_no}`}
                open={settleOpen}
                onOk={handleSettle}
                onCancel={() => { setSettleOpen(false); settleForm.resetFields(); }}
                destroyOnClose
                okText="确认核销"
            >
                {settlingBill && (
                    <div className="mb-4 p-3 bg-slate-800/50 rounded text-sm flex gap-6">
                        <span className="text-slate-400">单据余额: <span className="text-amber-400 font-mono">¥{parseFloat(settlingBill.balance).toFixed(2)}</span></span>
                    </div>
                )}
                <Form form={settleForm} layout="vertical">
                    <Form.Item name="ar_receipt_id" label="选择收款单" rules={[{ required: true }]}>
                        <Select options={openReceiptOptions} placeholder="选择有余额的收款单" showSearch />
                    </Form.Item>
                    <Form.Item name="amount" label="核销金额" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} min={0.01} precision={2} prefix="¥" />
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default AccountsReceivable;
