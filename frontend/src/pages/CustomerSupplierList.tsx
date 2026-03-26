import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Tag, Modal, Form, Input, message } from 'antd';
import { PlusOutlined, EditOutlined } from '@ant-design/icons';
import apiClient from '../api/client';

const { Title, Text } = Typography;

const CustomerSupplierList: React.FC = () => {
    const [data, setData] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [form] = Form.useForm();
    const [editingCode, setEditingCode] = useState<string | null>(null);

    const fetchData = () => {
        setLoading(true);
        // Using auxiliary items of specific categories: '客户' (Customer) and '供应商' (Supplier)
        apiClient.get('/auxiliary-items?limit=100').then(res => {
            const filtered = res.data.data.filter((item: any) =>
                item.category_code === '客户' || item.category_code === '供应商'
            );
            setData(filtered);
            setLoading(false);
        }).catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchData();
    }, []);

    const handleAdd = () => {
        setEditingCode(null);
        form.resetFields();
        setIsModalOpen(true);
    };

    const handleEdit = (record: any) => {
        setEditingCode(record.code);
        form.setFieldsValue(record);
        setIsModalOpen(true);
    };

    const handleOk = () => {
        form.validateFields().then(values => {
            const request = editingCode
                ? apiClient.patch(`/auxiliary-items/${editingCode}`, values)
                : apiClient.post('/auxiliary-items', values);

            request.then(() => {
                message.success('保存成功');
                setIsModalOpen(false);
                fetchData();
            }).catch(() => message.error('保存失败'));
        });
    };

    const columns = [
        { title: '类别', dataIndex: 'category_code', key: 'category_code', render: (text: string) => <Tag color={text === '客户' ? 'blue' : 'orange'}>{text}</Tag> },
        { title: '编码', dataIndex: 'code', key: 'code' },
        { title: '名称', dataIndex: 'name', key: 'name' },
        {
            title: '操作',
            key: 'action',
            render: (_: any, record: any) => (
                <Space>
                    <Button type="text" icon={<EditOutlined />} onClick={() => handleEdit(record)} />
                </Space>
            )
        },
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">客商管理</Title>
                    <Text className="text-slate-400">管理客户和供应商基本信息</Text>
                </div>
                <Button type="primary" icon={<PlusOutlined />} onClick={handleAdd}>新增客商</Button>
            </div>

            <Card className="glass-panel">
                <Table dataSource={data} columns={columns} loading={loading} rowKey="code" />
            </Card>

            <Modal
                title={editingCode ? "编辑客商" : "新增客商"}
                open={isModalOpen}
                onOk={handleOk}
                onCancel={() => setIsModalOpen(false)}
            >
                <Form form={form} layout="vertical">
                    <Form.Item name="category_code" label="类别" rules={[{ required: true }]}>
                        <Input placeholder="客户 或 供应商" />
                    </Form.Item>
                    <Form.Item name="code" label="编码" rules={[{ required: true }]}>
                        <Input disabled={!!editingCode} />
                    </Form.Item>
                    <Form.Item name="name" label="名称" rules={[{ required: true }]}>
                        <Input />
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default CustomerSupplierList;
