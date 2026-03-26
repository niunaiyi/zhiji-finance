import React, { useState, useEffect, useCallback } from 'react';
import {
    Card, Table, Button, Tag, Modal, Form, Input, InputNumber,
    DatePicker, Select, message, Tabs, Switch, Typography, Statistic, Row, Col
} from 'antd';
import type { TableProps } from 'antd';
import { PlusOutlined, LinkOutlined } from '@ant-design/icons';
import { apApi } from '../api/ap';
import type { ApBill, ApPayment } from '../api/ap';
import { periodsApi } from '../api/periods';
import type { Period } from '../types/period';

const { Title, Text } = Typography;

const STATUS_COLORS: Record<string, string> = {
    open: 'blue', partial: 'orange', settled: 'green', voided: 'red',
};
const STATUS_LABELS: Record<string, string> = {
    open: '未结清', partial: '部分结清', settled: '已结清', voided: '已作废',
};

const AccountsPayable: React.FC = () => {
    const [bills, setBills] = useState<ApBill[]>([]);
    const [payments, setPayments] = useState<ApPayment[]>([]);
    const [periods, setPeriods] = useState<Period[]>([]);
    const [loadingBills, setLoadingBills] = useState(false);
    const [loadingPayments, setLoadingPayments] = useState(false);

    const [settleOpen, setSettleOpen] = useState(false);
    const [settlingBill, setSettlingBill] = useState<ApBill | null>(null);
    const [settleForm] = Form.useForm();

    const [billModalOpen, setBillModalOpen] = useState(false);
    const [paymentModalOpen, setPaymentModalOpen] = useState(false);
    const [billForm] = Form.useForm();
    const [paymentForm] = Form.useForm();

    const fetchBills = useCallback(async () => {
        setLoadingBills(true);
        try {
            const resp = await apApi.listBills();
            setBills(resp.data?.data || []);
        } catch { message.error('加载应付单据失败'); }
        finally { setLoadingBills(false); }
    }, []);

    const fetchPayments = useCallback(async () => {
        setLoadingPayments(true);
        try {
            const resp = await apApi.listPayments();
            setPayments(resp.data?.data || []);
        } catch { message.error('加载付款单失败'); }
        finally { setLoadingPayments(false); }
    }, []);

    useEffect(() => {
        fetchBills();
        fetchPayments();
        periodsApi.list({ status: 'open' }).then(r => setPeriods(r.data || [])).catch(() => {});
    }, [fetchBills, fetchPayments]);

    const handleCreateBill = async () => {
        try {
            const v = await billForm.validateFields();
            await apApi.createBill({
                ...v,
                bill_date: v.bill_date.format('YYYY-MM-DD'),
                amount: Number(v.amount),
                is_estimate: v.is_estimate || false,
            });
            message.success('应付单据创建成功');
            setBillModalOpen(false);
            billForm.resetFields();
            fetchBills();
        } catch (err: any) {
            if (err?.response?.data?.message) message.error(err.response.data.message);
        }
    };

    const handleCreatePayment = async () => {
        try {
            const v = await paymentForm.validateFields();
            await apApi.createPayment({
                ...v,
                payment_date: v.payment_date.format('YYYY-MM-DD'),
                amount: Number(v.amount),
            });
            message.success('付款单创建成功');
            setPaymentModalOpen(false);
            paymentForm.resetFields();
            fetchPayments();
        } catch (err: any) {
            if (err?.response?.data?.message) message.error(err.response.data.message);
        }
    };

    const handleSettle = async () => {
        try {
            const v = await settleForm.validateFields();
            await apApi.settle(settlingBill!.id, v.ap_payment_id, Number(v.amount));
            message.success('核销成功');
            setSettleOpen(false);
            settleForm.resetFields();
            fetchBills();
            fetchPayments();
        } catch (err: any) {
            if (err?.response?.data?.message) message.error(err.response.data.message);
            else message.error('核销失败');
        }
    };

    const periodOptions = periods.map(p => ({
        value: p.id,
        label: `${p.fiscal_year}-${String(p.period_number).padStart(2, '0')}`,
    }));

    const openPaymentOptions = payments
        .filter(p => p.status !== 'settled')
        .map(p => ({
            value: p.id,
            label: `${p.payment_no} (余额: ¥${parseFloat(p.balance).toFixed(2)})`,
        }));

    const billColumns: TableProps<ApBill>['columns'] = [
        { title: '单据号', dataIndex: 'bill_no', key: 'bill_no', width: 140 },
        { title: '日期', dataIndex: 'bill_date', key: 'bill_date', width: 110 },
        { title: '供应商', key: 'supplier', render: (_, r) => r.supplier?.name || `#${r.supplier_id}` },
        {
            title: '暂估', dataIndex: 'is_estimate', key: 'is_estimate', width: 70,
            render: v => v ? <Tag color="purple">暂估</Tag> : null,
        },
        {
            title: '金额', dataIndex: 'amount', key: 'amount', align: 'right', width: 120,
            render: v => <span className="font-mono">¥{parseFloat(v).toFixed(2)}</span>,
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
                <Button size="small" type="primary" icon={<LinkOutlined />}
                    onClick={() => { setSettlingBill(record); settleForm.setFieldsValue({ amount: record.balance }); setSettleOpen(true); }}>
                    核销
                </Button>
            ) : null,
        },
    ];

    const paymentColumns: TableProps<ApPayment>['columns'] = [
        { title: '付款单号', dataIndex: 'payment_no', key: 'payment_no', width: 140 },
        { title: '日期', dataIndex: 'payment_date', key: 'payment_date', width: 110 },
        { title: '供应商', key: 'supplier', render: (_, r) => r.supplier?.name || `#${r.supplier_id}` },
        {
            title: '金额', dataIndex: 'amount', key: 'amount', align: 'right', width: 120,
            render: v => <span className="font-mono">¥{parseFloat(v).toFixed(2)}</span>,
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
    const openPaymentTotal = payments.filter(p => p.status !== 'settled').reduce((s, p) => s + parseFloat(p.balance), 0);

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} style={{ color: '#e2e8f0', margin: 0 }}>应付管理</Title>
                    <Text type="secondary">管理应付单据、付款单及核销操作</Text>
                </div>
            </div>

            <Row gutter={16}>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="未结应付余额" value={openBillTotal.toFixed(2)} prefix="¥"
                            valueStyle={{ color: '#cf1322' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="未使用付款" value={openPaymentTotal.toFixed(2)} prefix="¥"
                            valueStyle={{ color: '#d48806' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="暂估单数" value={bills.filter(b => b.is_estimate).length} />
                    </Card>
                </Col>
            </Row>

            <Tabs items={[
                {
                    key: 'bills',
                    label: `应付单据 (${bills.length})`,
                    children: (
                        <Card bordered={false} className="glass-panel rounded-xl"
                            extra={<Button type="primary" icon={<PlusOutlined />} onClick={() => setBillModalOpen(true)}>新建应付单</Button>}>
                            <Table<ApBill> columns={billColumns} dataSource={bills} loading={loadingBills}
                                rowKey="id" pagination={{ pageSize: 15 }} />
                        </Card>
                    ),
                },
                {
                    key: 'payments',
                    label: `付款单 (${payments.length})`,
                    children: (
                        <Card bordered={false} className="glass-panel rounded-xl"
                            extra={<Button type="primary" icon={<PlusOutlined />} onClick={() => setPaymentModalOpen(true)}>新建付款单</Button>}>
                            <Table<ApPayment> columns={paymentColumns} dataSource={payments} loading={loadingPayments}
                                rowKey="id" pagination={{ pageSize: 15 }} />
                        </Card>
                    ),
                },
            ]} />

            {/* Create Bill Modal */}
            <Modal title="新建应付单据" open={billModalOpen} onOk={handleCreateBill}
                onCancel={() => { setBillModalOpen(false); billForm.resetFields(); }} destroyOnClose>
                <Form form={billForm} layout="vertical">
                    <Form.Item name="bill_no" label="单据号" rules={[{ required: true }]}>
                        <Input placeholder="AP-2024-001" />
                    </Form.Item>
                    <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
                        <Select options={periodOptions} placeholder="选择期间" />
                    </Form.Item>
                    <Form.Item name="supplier_id" label="供应商 ID" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} placeholder="供应商辅助核算 ID" />
                    </Form.Item>
                    <Form.Item name="bill_date" label="单据日期" rules={[{ required: true }]}>
                        <DatePicker style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item name="amount" label="金额" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} min={0.01} precision={2} prefix="¥" />
                    </Form.Item>
                    <Form.Item name="is_estimate" label="暂估入账" valuePropName="checked">
                        <Switch checkedChildren="是" unCheckedChildren="否" />
                    </Form.Item>
                </Form>
            </Modal>

            {/* Create Payment Modal */}
            <Modal title="新建付款单" open={paymentModalOpen} onOk={handleCreatePayment}
                onCancel={() => { setPaymentModalOpen(false); paymentForm.resetFields(); }} destroyOnClose>
                <Form form={paymentForm} layout="vertical">
                    <Form.Item name="payment_no" label="付款单号" rules={[{ required: true }]}>
                        <Input placeholder="PAY-2024-001" />
                    </Form.Item>
                    <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
                        <Select options={periodOptions} placeholder="选择期间" />
                    </Form.Item>
                    <Form.Item name="supplier_id" label="供应商 ID" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} placeholder="供应商辅助核算 ID" />
                    </Form.Item>
                    <Form.Item name="payment_date" label="付款日期" rules={[{ required: true }]}>
                        <DatePicker style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item name="amount" label="金额" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} min={0.01} precision={2} prefix="¥" />
                    </Form.Item>
                </Form>
            </Modal>

            {/* Settlement Modal */}
            <Modal title={`核销应付单 — ${settlingBill?.bill_no}`} open={settleOpen} onOk={handleSettle}
                onCancel={() => { setSettleOpen(false); settleForm.resetFields(); }} destroyOnClose okText="确认核销">
                {settlingBill && (
                    <div className="mb-4 p-3 bg-slate-800/50 rounded text-sm">
                        <span className="text-slate-400">单据余额: <span className="text-amber-400 font-mono">¥{parseFloat(settlingBill.balance).toFixed(2)}</span></span>
                    </div>
                )}
                <Form form={settleForm} layout="vertical">
                    <Form.Item name="ap_payment_id" label="选择付款单" rules={[{ required: true }]}>
                        <Select options={openPaymentOptions} placeholder="选择有余额的付款单" showSearch />
                    </Form.Item>
                    <Form.Item name="amount" label="核销金额" rules={[{ required: true }]}>
                        <InputNumber style={{ width: '100%' }} min={0.01} precision={2} prefix="¥" />
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default AccountsPayable;
