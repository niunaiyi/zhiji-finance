import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Modal, Form, Input, message, Tabs, Empty, Spin } from 'antd';
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import { auxCategoriesApi, auxItemsApi } from '../api/auxiliary';
import { AuxCategory, AuxItem } from '../types/auxiliary';

const { Title, Text } = Typography;

const AuxiliaryManagement: React.FC = () => {
    const [categories, setCategories] = useState<AuxCategory[]>([]);
    const [activeCategory, setActiveCategory] = useState<number | null>(null);
    const [items, setItems] = useState<AuxItem[]>([]);
    const [loading, setLoading] = useState(false);
    const [categoriesLoading, setCategoriesLoading] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [form] = Form.useForm();
    const [editingId, setEditingId] = useState<number | null>(null);

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
        if (activeCategory) {
            form.setFieldsValue({ aux_category_id: activeCategory });
        }
        setIsModalOpen(true);
    };

    const handleEdit = (record: AuxItem) => {
        setEditingId(record.id);
        form.setFieldsValue({
            code: record.code,
            name: record.name,
            aux_category_id: record.aux_category_id,
        });
        setIsModalOpen(true);
    };

    const handleOk = async () => {
        try {
            const values = await form.validateFields();

            if (editingId) {
                await auxItemsApi.update(editingId, values);
            } else {
                await auxItemsApi.create(values);
            }

            message.success('保存成功');
            setIsModalOpen(false);
            if (activeCategory) {
                fetchItems(activeCategory);
            }
        } catch (error: unknown) {
            console.error('Failed to save item:', error);
            message.error('保存失败');
        }
    };

    const handleDelete = async (id: number) => {
        Modal.confirm({
            title: '确认删除',
            content: '确定要删除这个辅助核算项目吗？',
            okText: '确定',
            cancelText: '取消',
            onOk: async () => {
                try {
                    // Note: Delete API is not implemented in auxiliary.ts yet
                    // This will need to be added to the API module
                    message.warning('删除功能暂未实现');
                } catch (error: unknown) {
                    console.error('Failed to delete item:', error);
                    message.error('删除失败');
                }
            },
        });
    };

    const getCurrentCategory = () => {
        return categories.find(cat => cat.id === activeCategory);
    };

    const isSystemCategory = () => {
        const currentCategory = getCurrentCategory();
        return currentCategory?.is_system || false;
    };

    const columns = [
        { title: '编码', dataIndex: 'code', key: 'code', width: 200 },
        { title: '名称', dataIndex: 'name', key: 'name' },
        {
            title: '操作',
            key: 'action',
            width: 150,
            render: (_: unknown, record: AuxItem) => {
                const systemCategory = isSystemCategory();
                return (
                    <Space>
                        <Button
                            type="text"
                            icon={<EditOutlined />}
                            onClick={() => handleEdit(record)}
                            disabled={systemCategory}
                            title={systemCategory ? '系统类别不可编辑' : ''}
                        />
                        <Button
                            type="text"
                            danger
                            icon={<DeleteOutlined />}
                            onClick={() => handleDelete(record.id)}
                            disabled={systemCategory}
                            title={systemCategory ? '系统类别不可删除' : ''}
                        />
                    </Space>
                );
            }
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
                    disabled={!activeCategory || isSystemCategory()}
                    title={isSystemCategory() ? '系统类别不可新增项目' : ''}
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
                title={editingId ? `编辑${getCurrentCategory()?.name || ''}` : `新增${getCurrentCategory()?.name || ''}`}
                open={isModalOpen}
                onOk={handleOk}
                onCancel={() => setIsModalOpen(false)}
                destroyOnClose
            >
                <Form form={form} layout="vertical">
                    <Form.Item name="aux_category_id" label="类别" hidden>
                        <Input type="number" />
                    </Form.Item>
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
