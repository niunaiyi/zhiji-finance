import React, { useEffect, useState, useMemo } from 'react';
import { Card, Table, Button, Typography, Modal, Form, Input, message, Tabs, Empty, Spin } from 'antd';
import { PlusOutlined, EditOutlined } from '@ant-design/icons';
import { auxCategoriesApi, auxItemsApi } from '../api/auxiliary';
import type { AuxCategory, AuxItem } from '../types/auxiliary';

const { Title, Text } = Typography;

const AuxiliaryManagement: React.FC = () => {
    const [categories, setCategories] = useState<AuxCategory[]>([]);
    const [activeCategory, setActiveCategory] = useState<number | null>(null);
    const [items, setItems] = useState<AuxItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [categoriesLoading, setCategoriesLoading] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [form] = Form.useForm();
    const [editingId, setEditingId] = useState<number | null>(null);

    const currentCategory = useMemo(() =>
        categories.find(c => c.id === activeCategory),
        [categories, activeCategory]
    );

    const isSystemCategory = useMemo(() =>
        currentCategory?.is_system ?? false,
        [currentCategory]
    );

    const fetchCategories = async () => {
        setCategoriesLoading(true);
        try {
            const response = await auxCategoriesApi.list();
            setCategories(response.data);
            if (response.data.length > 0 && !activeCategory) {
                setActiveCategory(response.data[0].id);
            }
        } catch (error: unknown) {
            console.error('Failed to load categories:', error);
            message.error('获取类别失败');
        } finally {
            setCategoriesLoading(false);
        }
    };

    const fetchItems = async (categoryId: number) => {
        if (!categoryId) return;
        setLoading(true);
        try {
            const response = await auxItemsApi.list({ aux_category_id: categoryId });
            setItems(response.data);
        } catch (error: unknown) {
            console.error('Failed to load items:', error);
            message.error('获取项目失败');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    useEffect(() => {
        if (activeCategory) {
            fetchItems(activeCategory);
        }
    }, [activeCategory]);

    const handleAdd = () => {
        setEditingId(null);
        form.resetFields();
        setIsModalOpen(true);
    };

    const handleEdit = (record: AuxItem) => {
        setEditingId(record.id);
        form.setFieldsValue({
            code: record.code,
            name: record.name,
        });
        setIsModalOpen(true);
    };

    const handleOk = async () => {
        setSubmitting(true);
        try {
            const values = await form.validateFields();
            const payload = {
                ...values,
                aux_category_id: activeCategory!,
            };

            if (editingId) {
                await auxItemsApi.update(editingId, payload);
                message.success('更新成功');
            } else {
                await auxItemsApi.create(payload);
                message.success('创建成功');
            }

            setIsModalOpen(false);
            form.resetFields();
            fetchItems(activeCategory!);
        } catch (error: unknown) {
            console.error('Failed to save item:', error);
            message.error(editingId ? '更新失败' : '创建失败');
        } finally {
            setSubmitting(false);
        }
    };

    const columns = [
        { title: '编码', dataIndex: 'code', key: 'code', width: 200 },
        { title: '名称', dataIndex: 'name', key: 'name' },
        {
            title: '操作',
            key: 'action',
            width: 100,
            render: (_: unknown, record: AuxItem) => (
                <Button
                    type="text"
                    icon={<EditOutlined />}
                    onClick={() => handleEdit(record)}
                    disabled={isSystemCategory}
                    title={isSystemCategory ? '系统类别不可编辑' : ''}
                />
            )
        },
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">辅助核算管理</Title>
                    <Text className="text-slate-400">管理各维度的辅助核算资料</Text>
                </div>
                <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    size="large"
                    onClick={handleAdd}
                    disabled={!activeCategory || isSystemCategory}
                    title={isSystemCategory ? '系统类别不可新增项目' : ''}
                >
                    新增项目
                </Button>
            </div>

            <Card className="glass-panel rounded-xl">
                {categoriesLoading ? (
                    <div className="flex justify-center items-center py-12">
                        <Spin size="large" />
                    </div>
                ) : categories.length > 0 ? (
                    <Tabs
                        activeKey={activeCategory?.toString()}
                        onChange={(key) => setActiveCategory(Number(key))}
                        items={categories.map(cat => ({
                            key: cat.id.toString(),
                            label: (
                                <span>
                                    {cat.name}
                                    {cat.is_system && <Text type="secondary" className="ml-2 text-xs">(系统)</Text>}
                                </span>
                            ),
                            children: (
                                <Table
                                    dataSource={items}
                                    columns={columns}
                                    loading={loading}
                                    rowKey="id"
                                    className="mt-4"
                                />
                            )
                        }))}
                    />
                ) : (
                    <Empty description="暂无核算类别" />
                )}
            </Card>

            <Modal
                title={editingId ? `编辑${currentCategory?.name || ''}` : `新增${currentCategory?.name || ''}`}
                open={isModalOpen}
                onOk={handleOk}
                onCancel={() => setIsModalOpen(false)}
                confirmLoading={submitting}
                destroyOnClose
            >
                <Form form={form} layout="vertical">
                    <Form.Item name="code" label="编码" rules={[{ required: true, message: '请输入编码' }]}>
                        <Input placeholder="建议使用字母或数字组合" />
                    </Form.Item>
                    <Form.Item name="name" label="名称" rules={[{ required: true, message: '请输入名称' }]}>
                        <Input placeholder="请输入核算项名称" />
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default AuxiliaryManagement;
