import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Modal, Form, Input, message, Empty, InputNumber, Popconfirm } from 'antd';
import { PlusOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import apiClient from '../api/client';

const { Title, Text } = Typography;

interface DictionaryItem {
    code: string;
    name: string;
    sort_order: number;
}

interface DictionaryType {
    id: number;
    type: string;
    name: string;
    items: DictionaryItem[];
    is_system: boolean;
}

const DictionaryManagement: React.FC = () => {
    const [dictionaries, setDictionaries] = useState<DictionaryType[]>([]);
    const [loading, setLoading] = useState(false);
    const [isTypeModalOpen, setIsTypeModalOpen] = useState(false);
    const [editingType, setEditingType] = useState<DictionaryType | null>(null);
    const [typeForm] = Form.useForm();

    const [activeTypeId, setActiveTypeId] = useState<string>('');

    const fetchDictionaries = async () => {
        setLoading(true);
        try {
            const res = await apiClient.get('/dictionaries/all');
            setDictionaries(res.data.data);
            if (res.data.data.length > 0 && !activeTypeId) {
                setActiveTypeId(String(res.data.data[0].id));
            }
        } catch (error) {
            message.error('获取字典列表失败');
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchDictionaries();
    }, []);

    const handleAddType = () => {
        setEditingType(null);
        typeForm.resetFields();
        setIsTypeModalOpen(true);
    };

    const handleEditType = (dict: DictionaryType) => {
        setEditingType(dict);
        typeForm.setFieldsValue(dict);
        setIsTypeModalOpen(true);
    };

    const handleTypeOk = () => {
        typeForm.validateFields().then(values => {
            const request = editingType
                ? apiClient.patch(`/dictionaries/${editingType.id}`, values)
                : apiClient.post('/dictionaries', { ...values, items: [] });

            request.then(() => {
                message.success('保存成功');
                setIsTypeModalOpen(false);
                fetchDictionaries();
            }).catch(() => message.error('保存失败'));
        });
    };

    const handleDeleteType = (id: number) => {
        apiClient.delete(`/dictionaries/${id}`).then(() => {
            message.success('删除成功');
            fetchDictionaries();
            if (activeTypeId === String(id)) setActiveTypeId('');
        }).catch(() => message.error('删除失败 (系统内置项不可删除)'));
    };

    // --- Dictionary Items Logic ---
    const activeDict = dictionaries.find(d => String(d.id) === activeTypeId);

    const updateItems = (newItems: DictionaryItem[]) => {
        if (!activeDict) return;
        apiClient.patch(`/dictionaries/${activeDict.id}`, { items: newItems }).then(() => {
            message.success('字典项更新成功');
            fetchDictionaries();
        }).catch(() => message.error('更新失败'));
    };

    const handleAddItem = () => {
        if (!activeDict) return;
        const newItems = [...(activeDict.items || [])];
        const nextOrder = newItems.length > 0 ? Math.max(...newItems.map(i => i.sort_order)) + 10 : 10;
        newItems.push({ code: 'new_code', name: '新选项', sort_order: nextOrder });
        updateItems(newItems);
    };

    const handleRemoveItem = (code: string) => {
        if (!activeDict) return;
        const newItems = (activeDict.items || []).filter(i => i.code !== code);
        updateItems(newItems);
    };

    const itemColumns = [
        {
            title: '编码',
            dataIndex: 'code',
            key: 'code',
            render: (text: string, _record: any, index: number) => (
                <Input defaultValue={text} onBlur={(e) => {
                    if (e.target.value !== text) {
                        const newItems = [...(activeDict?.items || [])];
                        newItems[index].code = e.target.value;
                        updateItems(newItems);
                    }
                }} />
            )
        },
        {
            title: '名称',
            dataIndex: 'name',
            key: 'name',
            render: (text: string, _record: any, index: number) => (
                <Input defaultValue={text} onBlur={(e) => {
                    if (e.target.value !== text) {
                        const newItems = [...(activeDict?.items || [])];
                        newItems[index].name = e.target.value;
                        updateItems(newItems);
                    }
                }} />
            )
        },
        {
            title: '排序',
            dataIndex: 'sort_order',
            key: 'sort_order',
            width: 100,
            render: (val: number, _record: any, index: number) => (
                <InputNumber defaultValue={val} onChange={(newVal) => {
                    const newItems = [...(activeDict?.items || [])];
                    newItems[index].sort_order = newVal || 0;
                    // Sort order changes usually worth sorting immediately
                    newItems.sort((a, b) => a.sort_order - b.sort_order);
                    updateItems(newItems);
                }} />
            )
        },
        {
            title: '操作',
            key: 'action',
            width: 80,
            render: (_: any, record: DictionaryItem) => (
                <Popconfirm title="确定删除项吗?" onConfirm={() => handleRemoveItem(record.code)}>
                    <Button type="text" danger icon={<DeleteOutlined />} />
                </Popconfirm>
            )
        }
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">数据字典管理</Title>
                    <Text className="text-slate-400">管理系统所有的下拉选项和固定参数 (JSON 驱动)</Text>
                </div>
                <Space>
                    <Button type="primary" icon={<PlusOutlined />} size="large" onClick={handleAddType}>新增字典类别</Button>
                </Space>
            </div>

            <div className="grid grid-cols-12 gap-6">
                <Card className="col-span-4 glass-panel rounded-xl overflow-hidden" styles={{ body: { padding: 0 } }}>
                    <div className="p-4 border-b border-slate-700/50 flex justify-between items-center bg-slate-800/20">
                        <span className="font-bold text-slate-300">字典类别</span>
                    </div>
                    <Table
                        dataSource={dictionaries}
                        loading={loading}
                        pagination={false}
                        rowKey="id"
                        onRow={(record) => ({
                            onClick: () => setActiveTypeId(String(record.id)),
                            className: `cursor-pointer transition-colors ${activeTypeId === String(record.id) ? 'bg-amber-500/10' : 'hover:bg-slate-700/30'}`
                        })}
                        showHeader={false}
                        columns={[
                            {
                                render: (_, record) => (
                                    <div className="flex justify-between items-center w-full group">
                                        <div className="flex flex-col">
                                            <span className="font-medium text-slate-200">{record.name}</span>
                                            <span className="text-xs text-slate-500 font-mono">{record.type}</span>
                                        </div>
                                        <Space className="opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Button type="text" size="small" icon={<EditOutlined />} onClick={(e) => { e.stopPropagation(); handleEditType(record); }} />
                                            {!record.is_system && (
                                                <Button type="text" size="small" danger icon={<DeleteOutlined />} onClick={(e) => { e.stopPropagation(); handleDeleteType(record.id); }} />
                                            )}
                                        </Space>
                                    </div>
                                )
                            }
                        ]}
                    />
                </Card>

                <Card className="col-span-8 glass-panel rounded-xl">
                    {activeDict ? (
                        <div className="space-y-4">
                            <div className="flex justify-between items-center">
                                <div>
                                    <Title level={4} className="!text-slate-300 !mb-0">{activeDict.name} - 字典项</Title>
                                    <Text className="text-slate-500 text-xs font-mono">Type ID: {activeDict.type}</Text>
                                </div>
                                <Button type="dashed" icon={<PlusOutlined />} onClick={handleAddItem}>添加字典项</Button>
                            </div>
                            <Table
                                dataSource={[...(activeDict.items || [])].sort((a, b) => a.sort_order - b.sort_order)}
                                columns={itemColumns}
                                pagination={false}
                                rowKey="code"
                                size="small"
                                className="custom-table"
                            />
                        </div>
                    ) : (
                        <div className="h-64 flex items-center justify-center">
                            <Empty description={<span className="text-slate-500">请选择左侧字典类别进行管理</span>} />
                        </div>
                    )}
                </Card>
            </div>

            <Modal
                title={editingType ? '编辑字典类别' : '新增字典类别'}
                open={isTypeModalOpen}
                onOk={handleTypeOk}
                onCancel={() => setIsTypeModalOpen(false)}
                destroyOnClose
            >
                <Form form={typeForm} layout="vertical">
                    <Form.Item name="name" label="名称" rules={[{ required: true, message: '请输入显示名称' }]}>
                        <Input placeholder="如: 科目类别" />
                    </Form.Item>
                    <Form.Item name="type" label="唯一标识 (Type)" rules={[{ required: true, message: '请输入唯一标识' }]}>
                        <Input placeholder="如: subject_category" disabled={!!editingType} />
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default DictionaryManagement;
