import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Modal, Form, Input, InputNumber, message, Select, DatePicker } from 'antd';
import { PlusOutlined, ArrowUpOutlined, ArrowDownOutlined } from '@ant-design/icons';
import { inventoryApi, type InventoryItem } from '../api/inventory';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const InventoryManagement: React.FC = () => {
    const { currentBook } = useBook();
    const [items, setItems] = useState<InventoryItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [itemModalOpen, setItemModalOpen] = useState(false);
    const [stockModalOpen, setStockModalOpen] = useState(false);
    const [stockType, setStockType] = useState<'IN' | 'OUT'>('IN');
    const [form] = Form.useForm();
    const [stockForm] = Form.useForm();

    const fetchItems = () => {
        setLoading(true);
        inventoryApi.listItems({ limit: 100 }).then(res => {
            setItems(res.data.data || res.data); // Handle both wrapped and unwrapped data
            setLoading(false);
        }).catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchItems();
    }, []);

    const handleCreateItem = () => {
        form.validateFields().then(values => {
            inventoryApi.createItem(values).then(() => {
                message.success('创建成功');
                setItemModalOpen(false);
                fetchItems();
            }).catch(() => message.error('创建失败'));
        });
    };

    const handleStockAction = () => {
        stockForm.validateFields().then(values => {
            if (!currentBook) {
                message.warning('请先选择账簿');
                return;
            }
            
            const payload = {
                ...values,
                book_code: currentBook.code,
                record_date: values.record_date.format('YYYY-MM-DD'),
            };

            const action = stockType === 'IN' 
                ? inventoryApi.stockIn(payload) 
                : inventoryApi.stockOut(payload);

            action.then(() => {
                message.success('操作成功');
                setStockModalOpen(false);
                fetchItems();
            }).catch(err => {
                message.error(err.response?.data?.message || '操作失败');
            });
        });
    };

    const columns = [
        { title: 'SKU/编码', dataIndex: 'sku', key: 'sku' },
        { title: '名称', dataIndex: 'name', key: 'name' },
        { title: '单位', dataIndex: 'unit', key: 'unit' },
        { title: '库存量', dataIndex: 'current_quantity', key: 'current_quantity', render: (val: number) => <Text strong className="text-amber-500">{val}</Text> },
        { title: '加权平均单价', dataIndex: 'current_average_cost', key: 'current_average_cost', render: (val: number) => `¥${Number(val).toFixed(2)}` },
        {
            title: '操作',
            key: 'action',
            render: (_: any, record: any) => (
                <Space>
                    <Button size="small" icon={<ArrowUpOutlined />} onClick={() => { setStockType('IN'); stockForm.setFieldValue('item_id', record.id); setStockModalOpen(true); }}>入库</Button>
                    <Button size="small" icon={<ArrowDownOutlined />} onClick={() => { setStockType('OUT'); stockForm.setFieldValue('item_id', record.id); setStockModalOpen(true); }}>出库</Button>
                </Space>
            )
        },
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">存货管理</Title>
                    <p className="text-slate-400">管理商品档案及库存变动</p>
                </div>
                <Button type="primary" icon={<PlusOutlined />} onClick={() => { form.resetFields(); setItemModalOpen(true); }}>新增物品</Button>
            </div>

            <Card className="glass-panel">
                <Table dataSource={items} columns={columns} loading={loading} rowKey="id" />
            </Card>

            <Modal title="新增物品" open={itemModalOpen} onOk={handleCreateItem} onCancel={() => setItemModalOpen(false)}>
                <Form form={form} layout="vertical">
                    <Form.Item name="sku" label="SKU/编码" rules={[{ required: true }]}><Input /></Form.Item>
                    <Form.Item name="name" label="名称" rules={[{ required: true }]}><Input /></Form.Item>
                    <Form.Item name="unit" label="单位" initialValue="个"><Input /></Form.Item>
                    <Form.Item name="category" label="分类"><Input /></Form.Item>
                </Form>
            </Modal>

            <Modal title={stockType === 'IN' ? '入库操作' : '出库操作'} open={stockModalOpen} onOk={handleStockAction} onCancel={() => setStockModalOpen(false)}>
                <Form form={stockForm} layout="vertical">
                    <Form.Item name="item_id" label="选择物品" rules={[{ required: true }]}>
                        <Select options={items.map(i => ({ label: `${i.sku} ${i.name}`, value: i.id }))} />
                    </Form.Item>
                    <Form.Item name="record_date" label="日期" rules={[{ required: true }]} initialValue={dayjs()}>
                        <DatePicker style={{ width: '100%' }} />
                    </Form.Item>
                    <Form.Item name="quantity" label="数量" rules={[{ required: true }]}><InputNumber min={0.0001} style={{ width: '100%' }} /></Form.Item>
                    {stockType === 'IN' && (
                        <Form.Item name="unit_price" label="入库单价" rules={[{ required: true }]}><InputNumber min={0} style={{ width: '100%' }} /></Form.Item>
                    )}
                </Form>
            </Modal>
        </div>
    );
};

export default InventoryManagement;
