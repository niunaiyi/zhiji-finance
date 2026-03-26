import React, { useState, useEffect, useCallback } from 'react';
import {
    Card, Button, Table, Tag, Modal, Form, Select, message,
    Space, Alert, Typography, Spin, Statistic, Row, Col, Popconfirm
} from 'antd';
import type { TableProps } from 'antd';
import {
    LockOutlined, UnlockOutlined, PlusOutlined, CalendarOutlined
} from '@ant-design/icons';
import { periodsApi } from '../api/periods';
import type { Period } from '../types/period';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const STATUS_CONFIG: Record<string, { color: string; label: string }> = {
    open: { color: 'green', label: '开放' },
    closed: { color: 'orange', label: '已结账' },
    locked: { color: 'red', label: '已锁定' },
};

const MONTHS = Array.from({ length: 12 }, (_, i) => i + 1);
const YEARS = Array.from({ length: 5 }, (_, i) => dayjs().year() - 2 + i);

const PeriodEnd: React.FC = () => {
    const [periods, setPeriods] = useState<Period[]>([]);
    const [loading, setLoading] = useState(false);
    const [initModalOpen, setInitModalOpen] = useState(false);
    const [initLoading, setInitLoading] = useState(false);
    const [initForm] = Form.useForm();

    const openPeriods = periods.filter(p => p.status === 'open');
    const closedPeriods = periods.filter(p => p.status !== 'open');

    const fetchPeriods = useCallback(async () => {
        setLoading(true);
        try {
            const resp = await periodsApi.list();
            setPeriods(resp.data || []);
        } catch {
            message.error('加载会计期间失败');
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchPeriods();
    }, [fetchPeriods]);

    const handleClose = async (period: Period) => {
        Modal.confirm({
            title: `确定结账 ${period.fiscal_year}-${String(period.period_number).padStart(2, '0')}？`,
            content: '结账后该期间凭证将无法修改，请确保所有凭证已审核过账。',
            okText: '立即结账',
            okType: 'danger',
            onOk: async () => {
                try {
                    await periodsApi.close(period.id);
                    message.success('结账成功');
                    fetchPeriods();
                } catch (err: any) {
                    message.error(err?.response?.data?.message || '结账失败');
                }
            },
        });
    };

    const handleInit = async () => {
        try {
            const values = await initForm.validateFields();
            setInitLoading(true);
            await periodsApi.initializeFiscalYear({
                fiscal_year: values.fiscal_year,
                start_month: values.start_month || 1,
            });
            message.success(`${values.fiscal_year} 年度 12 个会计期间已初始化`);
            setInitModalOpen(false);
            initForm.resetFields();
            fetchPeriods();
        } catch (err: any) {
            if (err?.response?.data?.message) {
                message.error(err.response.data.message);
            }
        } finally {
            setInitLoading(false);
        }
    };

    const columns: TableProps<Period>['columns'] = [
        {
            title: '年度',
            dataIndex: 'fiscal_year',
            key: 'fiscal_year',
            width: 80,
        },
        {
            title: '期间',
            dataIndex: 'period_number',
            key: 'period_number',
            width: 70,
            render: (n) => <span className="font-mono">{String(n).padStart(2, '0')}</span>,
        },
        {
            title: '开始日期',
            dataIndex: 'start_date',
            key: 'start_date',
            width: 120,
        },
        {
            title: '结束日期',
            dataIndex: 'end_date',
            key: 'end_date',
            width: 120,
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
            title: '结账时间',
            dataIndex: 'closed_at',
            key: 'closed_at',
            render: (v) => v ? dayjs(v).format('YYYY-MM-DD HH:mm') : '-',
        },
        {
            title: '操作',
            key: 'action',
            width: 120,
            render: (_, record) => (
                <Space>
                    {record.status === 'open' && (
                        <Popconfirm
                            title={`结账 ${record.fiscal_year}-${String(record.period_number).padStart(2, '0')}`}
                            description="结账后无法撤销，请确认。"
                            onConfirm={() => handleClose(record)}
                        >
                            <Button size="small" type="primary" danger icon={<LockOutlined />}>
                                结账
                            </Button>
                        </Popconfirm>
                    )}
                    {record.status !== 'open' && (
                        <Tag color="default" icon={<UnlockOutlined />}>已锁</Tag>
                    )}
                </Space>
            ),
        },
    ];

    const currentYear = dayjs().year();
    const currentPeriod = periods.find(p =>
        p.fiscal_year === currentYear &&
        p.period_number === dayjs().month() + 1 &&
        p.status === 'open'
    );

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} style={{ color: '#e2e8f0', margin: 0 }}>会计期间管理</Title>
                    <Text type="secondary">管理各年度的会计期间及结账状态</Text>
                </div>
                <Button type="primary" icon={<PlusOutlined />} size="large" onClick={() => setInitModalOpen(true)}>
                    初始化年度
                </Button>
            </div>

            <Row gutter={16}>
                <Col span={6}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic
                            title="当前开放期间"
                            value={openPeriods.length}
                            suffix="个"
                            valueStyle={{ color: '#3f8600' }}
                        />
                    </Card>
                </Col>
                <Col span={6}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic
                            title="本月状态"
                            value={currentPeriod ? '开放中' : '未开放'}
                            prefix={<CalendarOutlined />}
                            valueStyle={{ color: currentPeriod ? '#3f8600' : '#cf1322' }}
                        />
                    </Card>
                </Col>
                <Col span={6}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic
                            title="已结账期间"
                            value={closedPeriods.length}
                            suffix="个"
                            valueStyle={{ color: '#d48806' }}
                        />
                    </Card>
                </Col>
            </Row>

            {periods.length === 0 && !loading && (
                <Alert
                    type="warning"
                    showIcon
                    message="尚未初始化会计期间"
                    description="请点击右上角「初始化年度」按钮，为当前财年创建 12 个月度期间。"
                />
            )}

            <Card bordered={false} className="glass-panel rounded-xl overflow-hidden">
                {loading ? (
                    <div className="flex justify-center py-12"><Spin size="large" /></div>
                ) : (
                    <Table<Period>
                        columns={columns}
                        dataSource={periods}
                        rowKey="id"
                        pagination={{ pageSize: 24, showSizeChanger: false }}
                        size="middle"
                    />
                )}
            </Card>

            {/* Initialize fiscal year modal */}
            <Modal
                title="初始化会计年度"
                open={initModalOpen}
                onOk={handleInit}
                onCancel={() => { setInitModalOpen(false); initForm.resetFields(); }}
                confirmLoading={initLoading}
                okText="初始化"
            >
                <Alert
                    className="mb-4"
                    type="info"
                    showIcon
                    message="初始化后将创建该年度 12 个月的会计期间（状态：开放）。"
                />
                <Form form={initForm} layout="vertical">
                    <Form.Item name="fiscal_year" label="财年" rules={[{ required: true }]} initialValue={currentYear}>
                        <Select>
                            {YEARS.map(y => (
                                <Select.Option key={y} value={y}>{y} 年</Select.Option>
                            ))}
                        </Select>
                    </Form.Item>
                    <Form.Item name="start_month" label="起始月份" initialValue={1}>
                        <Select>
                            {MONTHS.map(m => (
                                <Select.Option key={m} value={m}>{m} 月</Select.Option>
                            ))}
                        </Select>
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default PeriodEnd;
