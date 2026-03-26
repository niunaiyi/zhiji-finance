import React, { useEffect, useState, useCallback } from 'react';
import {
    Table, Space, Button, Card, Tag, Tooltip, Modal, Form, Input,
    DatePicker, InputNumber, Select, message, Popconfirm, Divider, Badge
} from 'antd';
import type { TableProps } from 'antd';
import {
    PlusOutlined, EyeOutlined, DeleteOutlined, MinusCircleOutlined,
    CheckOutlined, SendOutlined, RollbackOutlined, StopOutlined, SearchOutlined
} from '@ant-design/icons';
import { vouchersApi } from '../api/vouchers';
import { accountsApi } from '../api/accounts';
import { periodsApi } from '../api/periods';
import type { Voucher, CreateVoucherRequest } from '../api/vouchers';
import type { Account } from '../types/account';
import type { Period } from '../types/period';
import dayjs from 'dayjs';

const STATUS_CONFIG: Record<string, { color: string; label: string }> = {
    draft: { color: 'default', label: '草稿' },
    reviewed: { color: 'blue', label: '已审核' },
    posted: { color: 'green', label: '已记账' },
    reversed: { color: 'orange', label: '已红冲' },
    voided: { color: 'red', label: '已作废' },
};

const TYPE_LABELS: Record<string, string> = {
    receipt: '收款',
    payment: '付款',
    transfer: '转账',
};

const Vouchers: React.FC = () => {
    const [vouchers, setVouchers] = useState<Voucher[]>([]);
    const [loading, setLoading] = useState(false);
    const [accounts, setAccounts] = useState<Account[]>([]);
    const [periods, setPeriods] = useState<Period[]>([]);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [form] = Form.useForm();
    const [filterForm] = Form.useForm();
    const [detailVoucher, setDetailVoucher] = useState<Voucher | null>(null);
    const [detailOpen, setDetailOpen] = useState(false);

    // Live debit/credit totals
    const entries = Form.useWatch('lines', form) || [];
    const debitTotal = entries.reduce((s: number, e: any) => s + (Number(e?.debit) || 0), 0);
    const creditTotal = entries.reduce((s: number, e: any) => s + (Number(e?.credit) || 0), 0);
    const isBalanced = debitTotal > 0 && Math.abs(debitTotal - creditTotal) < 0.001;

    const fetchVouchers = useCallback(async (filters?: any) => {
        setLoading(true);
        try {
            const params: any = { ...filters };
            const resp = await vouchersApi.list(params);
            setVouchers(resp.data?.data || []);
        } catch {
            message.error('加载凭证失败');
        } finally {
            setLoading(false);
        }
    }, []);

    const fetchAccounts = useCallback(async () => {
        try {
            const resp = await accountsApi.list();
            // Only leaf (detail) accounts can be used in voucher lines
            const all: Account[] = resp.data;
            setAccounts(all.filter(a => a.is_detail && a.is_active));
        } catch {
            /* ignore */
        }
    }, []);

    const fetchPeriods = useCallback(async () => {
        try {
            const resp = await periodsApi.list({ status: 'open' });
            setPeriods(resp.data || []);
        } catch {
            /* ignore */
        }
    }, []);

    useEffect(() => {
        fetchVouchers();
        fetchAccounts();
        fetchPeriods();
    }, [fetchVouchers, fetchAccounts, fetchPeriods]);

    const openCreate = () => {
        form.resetFields();
        form.setFieldsValue({
            voucher_date: dayjs(),
            lines: [
                { debit: undefined, credit: undefined },
                { debit: undefined, credit: undefined },
            ],
        });
        setIsModalOpen(true);
    };

    const handleCreate = async () => {
        try {
            const values = await form.validateFields();
            if (!isBalanced) {
                message.error(`借贷不平衡：借方 ¥${debitTotal.toFixed(2)}，贷方 ¥${creditTotal.toFixed(2)}`);
                return;
            }
            const payload: CreateVoucherRequest = {
                period_id: values.period_id,
                voucher_type: values.voucher_type,
                voucher_date: values.voucher_date.format('YYYY-MM-DD'),
                summary: values.summary,
                lines: values.lines.map((l: any) => ({
                    account_id: l.account_id,
                    summary: l.summary || '',
                    debit: String(l.debit || 0),
                    credit: String(l.credit || 0),
                })),
            };
            await vouchersApi.create(payload);
            message.success('凭证已保存');
            setIsModalOpen(false);
            fetchVouchers();
        } catch (err: any) {
            if (err?.response?.data?.message) {
                message.error(err.response.data.message);
            }
        }
    };

    const handleReview = async (id: number) => {
        try {
            await vouchersApi.review(id);
            message.success('审核成功');
            fetchVouchers();
        } catch {
            message.error('审核失败');
        }
    };

    const handlePost = async (id: number) => {
        try {
            await vouchersApi.post(id);
            message.success('过账成功');
            fetchVouchers();
        } catch {
            message.error('过账失败');
        }
    };

    const handleReverse = async (id: number) => {
        Modal.confirm({
            title: '确定红冲该凭证？',
            content: '红冲将生成一张金额相反的新凭证，原凭证标记为已红冲。',
            okText: '确定红冲',
            okType: 'danger',
            onOk: async () => {
                try {
                    await vouchersApi.reverse(id);
                    message.success('红冲成功');
                    fetchVouchers();
                } catch {
                    message.error('红冲失败');
                }
            },
        });
    };

    const handleVoid = async (id: number) => {
        try {
            await vouchersApi.void(id);
            message.success('凭证已作废');
            fetchVouchers();
        } catch {
            message.error('作废失败');
        }
    };

    const columns: TableProps<Voucher>['columns'] = [
        {
            title: '凭证号',
            dataIndex: 'voucher_no',
            key: 'voucher_no',
            width: 140,
            render: (text) => <Tag color="blue" className="font-mono">{text}</Tag>,
        },
        {
            title: '日期',
            dataIndex: 'voucher_date',
            key: 'voucher_date',
            width: 110,
        },
        {
            title: '类型',
            dataIndex: 'voucher_type',
            key: 'voucher_type',
            width: 80,
            render: (t) => TYPE_LABELS[t] || t,
        },
        {
            title: '摘要',
            dataIndex: 'summary',
            key: 'summary',
            ellipsis: true,
        },
        {
            title: '借方金额',
            dataIndex: 'total_debit',
            key: 'total_debit',
            align: 'right',
            width: 130,
            render: (v) => <span className="font-mono text-emerald-400">¥{parseFloat(v || 0).toFixed(2)}</span>,
        },
        {
            title: '贷方金额',
            dataIndex: 'total_credit',
            key: 'total_credit',
            align: 'right',
            width: 130,
            render: (v) => <span className="font-mono text-amber-400">¥{parseFloat(v || 0).toFixed(2)}</span>,
        },
        {
            title: '状态',
            dataIndex: 'status',
            key: 'status',
            width: 90,
            render: (s) => {
                const cfg = STATUS_CONFIG[s] || { color: 'default', label: s };
                return <Tag color={cfg.color}>{cfg.label}</Tag>;
            },
        },
        {
            title: '操作',
            key: 'action',
            width: 220,
            align: 'center',
            render: (_, record) => (
                <Space size="small">
                    <Tooltip title="查看详情">
                        <Button
                            type="text" shape="circle" size="small"
                            icon={<EyeOutlined />}
                            onClick={() => { setDetailVoucher(record); setDetailOpen(true); }}
                        />
                    </Tooltip>
                    {record.status === 'draft' && (
                        <>
                            <Tooltip title="审核">
                                <Button type="text" shape="circle" size="small"
                                    icon={<CheckOutlined />}
                                    className="text-blue-400"
                                    onClick={() => handleReview(record.id)} />
                            </Tooltip>
                            <Tooltip title="作废">
                                <Popconfirm title="确定作废？" onConfirm={() => handleVoid(record.id)}>
                                    <Button type="text" shape="circle" size="small"
                                        icon={<StopOutlined />}
                                        className="text-red-400" />
                                </Popconfirm>
                            </Tooltip>
                        </>
                    )}
                    {record.status === 'reviewed' && (
                        <>
                            <Tooltip title="过账">
                                <Button type="text" shape="circle" size="small"
                                    icon={<SendOutlined />}
                                    className="text-green-400"
                                    onClick={() => handlePost(record.id)} />
                            </Tooltip>
                            <Tooltip title="作废">
                                <Popconfirm title="确定作废？" onConfirm={() => handleVoid(record.id)}>
                                    <Button type="text" shape="circle" size="small"
                                        icon={<DeleteOutlined />}
                                        className="text-red-400" />
                                </Popconfirm>
                            </Tooltip>
                        </>
                    )}
                    {record.status === 'posted' && (
                        <Tooltip title="红冲">
                            <Button type="text" shape="circle" size="small"
                                icon={<RollbackOutlined />}
                                className="text-orange-400"
                                onClick={() => handleReverse(record.id)} />
                        </Tooltip>
                    )}
                </Space>
            ),
        },
    ];

    const accountOptions = accounts.map(a => ({
        value: a.id,
        label: `${a.code}  ${a.name}`,
    }));

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-slate-200">凭证管理</h2>
                    <p className="text-slate-400">填制、审核、过账会计凭证</p>
                </div>
                <Button type="primary" icon={<PlusOutlined />} size="large" onClick={openCreate}>
                    新建凭证
                </Button>
            </div>

            {/* Filters */}
            <Card bordered={false} className="glass-panel rounded-xl">
                <Form form={filterForm} layout="inline" onFinish={fetchVouchers}>
                    <Form.Item name="voucher_date_from" label={<span className="text-slate-400">开始日期</span>}>
                        <DatePicker placeholder="开始日期" />
                    </Form.Item>
                    <Form.Item name="voucher_date_to" label={<span className="text-slate-400">结束日期</span>}>
                        <DatePicker placeholder="结束日期" />
                    </Form.Item>
                    <Form.Item name="status" label={<span className="text-slate-400">状态</span>}>
                        <Select allowClear placeholder="全部" style={{ width: 120 }}>
                            {Object.entries(STATUS_CONFIG).map(([k, v]) => (
                                <Select.Option key={k} value={k}>{v.label}</Select.Option>
                            ))}
                        </Select>
                    </Form.Item>
                    <Form.Item>
                        <Space>
                            <Button type="primary" htmlType="submit" icon={<SearchOutlined />}>查询</Button>
                            <Button onClick={() => { filterForm.resetFields(); fetchVouchers(); }}>重置</Button>
                        </Space>
                    </Form.Item>
                </Form>
            </Card>

            {/* List */}
            <Card bordered={false} className="glass-panel rounded-xl overflow-hidden">
                <Table<Voucher>
                    columns={columns}
                    dataSource={vouchers}
                    loading={loading}
                    rowKey="id"
                    pagination={{ pageSize: 15, showSizeChanger: true, showTotal: t => `共 ${t} 条` }}
                    scroll={{ y: 'calc(100vh - 440px)' }}
                />
            </Card>

            {/* Create Modal */}
            <Modal
                title="新建凭证"
                open={isModalOpen}
                onOk={handleCreate}
                onCancel={() => setIsModalOpen(false)}
                width={960}
                destroyOnClose
                maskClosable={false}
                okText="保存草稿"
            >
                <Form form={form} layout="vertical">
                    <div className="grid grid-cols-4 gap-4 mb-2">
                        <Form.Item name="period_id" label="会计期间" rules={[{ required: true, message: '请选择期间' }]}>
                            <Select placeholder="选择期间" loading={periods.length === 0}>
                                {periods.map(p => (
                                    <Select.Option key={p.id} value={p.id}>
                                        {p.fiscal_year}-{String(p.period_number).padStart(2, '0')}
                                    </Select.Option>
                                ))}
                            </Select>
                        </Form.Item>
                        <Form.Item name="voucher_type" label="凭证类型" rules={[{ required: true, message: '请选择类型' }]}>
                            <Select>
                                <Select.Option value="receipt">收款凭证</Select.Option>
                                <Select.Option value="payment">付款凭证</Select.Option>
                                <Select.Option value="transfer">转账凭证</Select.Option>
                            </Select>
                        </Form.Item>
                        <Form.Item name="voucher_date" label="记账日期" rules={[{ required: true }]}>
                            <DatePicker style={{ width: '100%' }} />
                        </Form.Item>
                        <Form.Item name="summary" label="摘要">
                            <Input placeholder="凭证摘要（可选）" />
                        </Form.Item>
                    </div>

                    <Divider style={{ borderColor: 'rgba(255,255,255,0.1)', color: '#94A3B8', margin: '8px 0 12px' }}>
                        分录明细
                    </Divider>

                    <Form.List name="lines">
                        {(fields, { add, remove }) => (
                            <>
                                <div className="grid grid-cols-12 gap-2 mb-1 text-xs text-slate-500 px-2">
                                    <div className="col-span-3">摘要</div>
                                    <div className="col-span-4">科目</div>
                                    <div className="col-span-2 text-right">借方金额</div>
                                    <div className="col-span-2 text-right">贷方金额</div>
                                    <div className="col-span-1" />
                                </div>
                                {fields.map(({ key, name }) => (
                                    <div key={key} className="grid grid-cols-12 gap-2 mb-2 items-center">
                                        <Form.Item name={[name, 'summary']} className="col-span-3 mb-0">
                                            <Input placeholder="行摘要" />
                                        </Form.Item>
                                        <Form.Item name={[name, 'account_id']} className="col-span-4 mb-0"
                                            rules={[{ required: true, message: '请选择科目' }]}>
                                            <Select
                                                showSearch placeholder="选择科目"
                                                optionFilterProp="label"
                                                options={accountOptions}
                                            />
                                        </Form.Item>
                                        <Form.Item name={[name, 'debit']} className="col-span-2 mb-0">
                                            <InputNumber
                                                placeholder="借方"
                                                min={0} precision={2}
                                                style={{ width: '100%' }}
                                                className="text-right"
                                            />
                                        </Form.Item>
                                        <Form.Item name={[name, 'credit']} className="col-span-2 mb-0">
                                            <InputNumber
                                                placeholder="贷方"
                                                min={0} precision={2}
                                                style={{ width: '100%' }}
                                                className="text-right"
                                            />
                                        </Form.Item>
                                        <div className="col-span-1 flex justify-center">
                                            <Button
                                                type="text" danger size="small"
                                                icon={<MinusCircleOutlined />}
                                                onClick={() => remove(name)}
                                                disabled={fields.length <= 2}
                                            />
                                        </div>
                                    </div>
                                ))}

                                {/* Balance bar */}
                                <div className={`flex justify-end gap-8 p-3 rounded-lg mt-2 font-mono text-sm ${isBalanced ? 'bg-emerald-900/30 border border-emerald-700/50' : 'bg-slate-800/50 border border-slate-700/30'}`}>
                                    <span className="text-slate-400">借方合计: <span className="text-emerald-400 text-base font-semibold">¥{debitTotal.toFixed(2)}</span></span>
                                    <span className="text-slate-400">贷方合计: <span className="text-amber-400 text-base font-semibold">¥{creditTotal.toFixed(2)}</span></span>
                                    {isBalanced
                                        ? <Badge status="success" text={<span className="text-emerald-400 text-xs">借贷平衡 ✓</span>} />
                                        : <Badge status="error" text={<span className="text-red-400 text-xs">差额: ¥{Math.abs(debitTotal - creditTotal).toFixed(2)}</span>} />
                                    }
                                </div>

                                <Button
                                    type="dashed" block icon={<PlusOutlined />}
                                    className="mt-2 border-slate-700 text-slate-400"
                                    onClick={() => add({ debit: undefined, credit: undefined })}
                                >
                                    添加分录行
                                </Button>
                            </>
                        )}
                    </Form.List>
                </Form>
            </Modal>

            {/* Detail Modal */}
            <Modal
                title={`凭证详情 — ${detailVoucher?.voucher_no}`}
                open={detailOpen}
                onCancel={() => setDetailOpen(false)}
                footer={[
                    <Button key="close" onClick={() => setDetailOpen(false)}>关闭</Button>,
                    <Button key="print" type="primary" onClick={() => window.print()}>打印</Button>,
                ]}
                width={800}
            >
                {detailVoucher && (
                    <div className="space-y-4">
                        <div className="grid grid-cols-3 gap-4 text-sm text-slate-300">
                            <div><span className="text-slate-500">日期：</span>{detailVoucher.voucher_date}</div>
                            <div><span className="text-slate-500">类型：</span>{TYPE_LABELS[detailVoucher.voucher_type]}</div>
                            <div><span className="text-slate-500">状态：</span>
                                <Tag color={STATUS_CONFIG[detailVoucher.status]?.color}>
                                    {STATUS_CONFIG[detailVoucher.status]?.label}
                                </Tag>
                            </div>
                        </div>
                        {detailVoucher.summary && <p className="text-slate-300 text-sm">{detailVoucher.summary}</p>}
                        <Table
                            dataSource={detailVoucher.lines}
                            rowKey={(_, i) => String(i)}
                            pagination={false}
                            size="small"
                            columns={[
                                { title: '摘要', dataIndex: 'summary' },
                                { title: '科目', dataIndex: 'account_id',
                                    render: id => accounts.find(a => a.id === id)?.name || `#${id}` },
                                { title: '借方', dataIndex: 'debit', align: 'right',
                                    render: v => <span className="font-mono text-emerald-400">¥{parseFloat(v||0).toFixed(2)}</span> },
                                { title: '贷方', dataIndex: 'credit', align: 'right',
                                    render: v => <span className="font-mono text-amber-400">¥{parseFloat(v||0).toFixed(2)}</span> },
                            ]}
                        />
                        <div className="flex justify-end gap-8 p-3 bg-slate-800/50 rounded font-mono text-sm">
                            <span>借方: ¥{parseFloat(detailVoucher.total_debit).toFixed(2)}</span>
                            <span>贷方: ¥{parseFloat(detailVoucher.total_credit).toFixed(2)}</span>
                        </div>
                    </div>
                )}
            </Modal>
        </div>
    );
};

export default Vouchers;
