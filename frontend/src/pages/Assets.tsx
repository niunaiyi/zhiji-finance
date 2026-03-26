import React, { useEffect, useState } from 'react';
import { Table, Button, Card, Tag, Modal, Form, Input, DatePicker, InputNumber, Select, message, Popconfirm, Statistic, Row, Col } from 'antd';
import type { TableProps } from 'antd';
import { PlusOutlined, EditOutlined, DeleteOutlined, CalculatorOutlined } from '@ant-design/icons';
import { fixedAssetsApi, type FixedAsset } from '../api/fixedAssets';
import dayjs from 'dayjs';
import { useBook } from '../context/BookContext';

const Assets: React.FC = () => {
    const { currentBook } = useBook();
    const [data, setData] = useState<FixedAsset[]>([]);
    const [loading, setLoading] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [form] = Form.useForm();
    const [editingId, setEditingId] = useState<number | null>(null);

    // Depreciation Preview
    const [depreciationModalOpen, setDepreciationModalOpen] = useState(false);
    const [previewLoading, setPreviewLoading] = useState(false);
    const [previewData, setPreviewData] = useState<any[]>([]);
    const [totalDepreciation, setTotalDepreciation] = useState(0);

    const fetchAssets = () => {
        setLoading(true);
        fixedAssetsApi.listAssets({ book_code: currentBook?.code }).then(res => {
            setData(res.data.data || res.data);
            setLoading(false);
        }).catch(() => {
            setLoading(false);
        });
    };

    useEffect(() => {
        fetchAssets();
    }, [currentBook]);

    const handleAdd = () => {
        setEditingId(null);
        form.resetFields();
        form.setFieldsValue({
            purchase_date: dayjs(),
            residual_rate: 0.05,
            useful_life_months: 60,
            status: 'in_use',
            accumulated_depreciation: 0,
            depreciation_method: 'STRAIGHT_LINE'
        });
        setIsModalOpen(true);
    };

    const handleEdit = (record: FixedAsset) => {
        setEditingId(record.id);
        form.setFieldsValue({
            ...record,
            purchase_date: dayjs(record.purchase_date),
        });
        setIsModalOpen(true);
    };

    const handleDelete = (id: number) => {
        fixedAssetsApi.deleteAsset(id).then(() => {
            message.success('删除成功');
            fetchAssets();
        }).catch(() => {
            message.error('删除失败');
        });
    };

    const handleSave = () => {
        form.validateFields().then(values => {
            const payload = {
                ...values,
                book_code: currentBook?.code,
                purchase_date: values.purchase_date.format('YYYY-MM-DD'),
            };

            const request = editingId
                ? fixedAssetsApi.updateAsset(editingId, payload)
                : fixedAssetsApi.createAsset(payload);

            request.then(() => {
                message.success('保存成功');
                setIsModalOpen(false);
                fetchAssets();
            }).catch(err => {
                message.error(err.response?.data?.message || '保存失败');
            });
        });
    };

    const handlePreviewDepreciation = () => {
        setPreviewLoading(true);
        fixedAssetsApi.calculateDepreciation().then(res => {
            setPreviewData(res.data.preview || res.data.data || []);
            setTotalDepreciation(res.data.total_amount || 0);
            setDepreciationModalOpen(true);
        }).finally(() => {
            setPreviewLoading(false);
        });
    };

    const handleGenerateVoucher = () => {
        setLoading(true);
        fixedAssetsApi.generateVoucher({ date: dayjs().format('YYYY-MM-DD') }).then(() => {
            message.success('凭证生成成功');
            setDepreciationModalOpen(false);
        }).catch(err => {
            message.error(err.response?.data?.message || '生成失败');
        }).finally(() => {
            setLoading(false);
        });
    };

    const columns: TableProps<FixedAsset>['columns'] = [
        {
            title: '资产编号',
            dataIndex: 'asset_no',
            key: 'asset_no',
            render: (text) => <span className="font-mono text-slate-300">{text}</span>
        },
        {
            title: '资产名称',
            dataIndex: 'name',
            key: 'name',
            render: (text) => <span className="font-bold text-slate-200">{text}</span>
        },
        {
            title: '类别',
            dataIndex: 'category',
            key: 'category',
        },
        {
            title: '原值',
            dataIndex: 'original_value',
            key: 'original_value',
            align: 'right',
            render: (val) => <span className="font-mono text-emerald-400">{Number(val || 0).toLocaleString()}</span>
        },
        {
            title: '累计折旧',
            dataIndex: 'accumulated_depreciation',
            key: 'accumulated_depreciation',
            align: 'right',
            render: (val) => <span className="font-mono text-amber-500">{Number(val || 0).toLocaleString()}</span>
        },
        {
            title: '净值',
            key: 'net_value',
            align: 'right',
            render: (_: any, record: FixedAsset) => <span className="font-mono text-emerald-600 font-bold">{(Number(record.original_value) - Number(record.accumulated_depreciation) || 0).toLocaleString()}</span>
        },
        {
            title: '状态',
            dataIndex: 'status',
            key: 'status',
            render: (status) => <Tag color={status === 'in_use' ? 'success' : 'default'}>{status === 'in_use' ? '使用中' : status}</Tag>
        },
        {
            title: '操作',
            key: 'action',
            render: (_, record) => (
                <div className="flex gap-2">
                    <Button type="text" icon={<EditOutlined />} onClick={() => handleEdit(record)} />
                    <Popconfirm title="确定删除?" onConfirm={() => handleDelete(record.id)}>
                        <Button type="text" danger icon={<DeleteOutlined />} />
                    </Popconfirm>
                </div>
            )
        }
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-slate-200">固定资产</h2>
                    <p className="text-slate-400">管理资产卡片与折旧</p>
                </div>
                <div className="flex gap-2">
                    <Button icon={<CalculatorOutlined />} onClick={handlePreviewDepreciation} loading={previewLoading}>
                        折旧测算
                    </Button>
                    <Button type="primary" icon={<PlusOutlined />} onClick={handleAdd}>
                        新增资产
                    </Button>
                </div>
            </div>

            <Row gutter={16} className="mb-6">
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="资产总值" value={data.reduce((sum, item) => sum + Number(item.original_value), 0)} precision={2} prefix="¥" valueStyle={{ color: '#10b981' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="累计折旧" value={data.reduce((sum, item) => sum + Number(item.accumulated_depreciation), 0)} precision={2} prefix="¥" valueStyle={{ color: '#f59e0b' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card bordered={false} className="glass-panel">
                        <Statistic title="在用资产数" value={data.filter(i => i.status === 'in_use').length} valueStyle={{ color: '#3b82f6' }} />
                    </Card>
                </Col>
            </Row>

            <Card bordered={false} className="glass-panel rounded-xl overflow-hidden">
                <Table
                    columns={columns}
                    dataSource={data}
                    loading={loading}
                    rowKey="id"
                />
            </Card>

            {/* Edit Modal */}
            <Modal
                title={editingId ? "编辑资产" : "新增资产"}
                open={isModalOpen}
                onOk={handleSave}
                onCancel={() => setIsModalOpen(false)}
                width={800}
                destroyOnClose
            >
                <Form form={form} layout="vertical">
                    <div className="grid grid-cols-2 gap-4">
                        <Form.Item name="asset_no" label="资产编号" rules={[{ required: true }]}>
                            <Input />
                        </Form.Item>
                        <Form.Item name="name" label="资产名称" rules={[{ required: true }]}>
                            <Input />
                        </Form.Item>
                        <Form.Item name="category" label="资产类别" rules={[{ required: true }]}>
                            {/* Mock categories for now */}
                            <Select options={[
                                { label: '电子设备', value: 'ELECTRONIC' },
                                { label: '运输工具', value: 'VEHICLE' },
                                { label: '办公家具', value: 'FURNITURE' },
                            ]} />
                        </Form.Item>
                        <Form.Item name="department_code" label="使用部门">
                            <Input />
                        </Form.Item>
                        <Form.Item name="purchase_date" label="购置日期" rules={[{ required: true }]}>
                            <DatePicker style={{ width: '100%' }} />
                        </Form.Item>
                        <Form.Item name="start_use_date" label="开始使用日期" rules={[{ required: true }]}>
                            <DatePicker style={{ width: '100%' }} />
                        </Form.Item>
                        <Form.Item name="original_value" label="原值" rules={[{ required: true }]}>
                            <InputNumber style={{ width: '100%' }} min={0} precision={2} />
                        </Form.Item>
                        <Form.Item name="residual_rate" label="残值率 (0-1)" rules={[{ required: true }]}>
                            <InputNumber style={{ width: '100%' }} min={0} max={1} step={0.01} precision={4} />
                        </Form.Item>
                        <Form.Item name="useful_life_months" label="使用月数" rules={[{ required: true }]}>
                            <InputNumber style={{ width: '100%' }} min={1} />
                        </Form.Item>
                        <Form.Item name="accumulated_depreciation" label="初始累计折旧">
                            <InputNumber style={{ width: '100%' }} min={0} precision={2} />
                        </Form.Item>
                        <Form.Item name="status" label="状态">
                            <Select options={[
                                { label: '使用中', value: 'in_use' },
                                { label: '已报废', value: 'scrapped' },
                                { label: '已出售', value: 'sold' },
                            ]} />
                        </Form.Item>
                    </div>
                </Form>
            </Modal>

            {/* Depreciation Modal */}
            <Modal
                title="折旧测算"
                open={depreciationModalOpen}
                onCancel={() => setDepreciationModalOpen(false)}
                footer={[
                    <Button key="close" onClick={() => setDepreciationModalOpen(false)}>关闭</Button>,
                    <Button key="generate" type="primary" onClick={handleGenerateVoucher} loading={loading}>生成凭证</Button>
                ]}
                width={700}
            >
                <div className="mb-4 text-center">
                    <Statistic title="本期折旧总额" value={totalDepreciation} precision={2} valueStyle={{ color: '#f59e0b' }} prefix="¥" />
                </div>
                <Table
                    dataSource={previewData}
                    rowKey="asset_code"
                    pagination={false}
                    size="small"
                    columns={[
                        { title: '资产编号', dataIndex: 'asset_code' },
                        { title: '资产名称', dataIndex: 'asset_name' },
                        { title: '部门', dataIndex: 'department' },
                        { title: '本期折旧', dataIndex: 'amount', align: 'right', render: (val) => (val || 0).toLocaleString() },
                    ]}
                />
            </Modal>
        </div>
    );
};

export default Assets;
