import React, { useEffect, useState, useCallback } from 'react';
import { Table, Tag, Button, Card, Tooltip, Space, Modal, Form, Input, Select, Radio, message, Popconfirm } from 'antd';
import type { TableProps } from 'antd';
import { PlusOutlined, EditOutlined, DeleteOutlined, SearchOutlined, VerticalAlignBottomOutlined, VerticalAlignTopOutlined } from '@ant-design/icons';
import { accountsApi } from '../api/accounts';
import type { Account, CreateAccountRequest, UpdateAccountRequest } from '../types/account';
import { useBook } from '../context/BookContext';

interface AccountTreeNode extends Account {
    children?: AccountTreeNode[];
}

// Element type display mapping
const ELEMENT_TYPE_LABELS: Record<string, string> = {
    asset: '资产',
    liability: '负债',
    equity: '权益',
    income: '收入',
    expense: '费用',
    cost: '成本'
};

// Balance direction display mapping
const BALANCE_DIRECTION_LABELS: Record<string, string> = {
    debit: '借',
    credit: '贷'
};

// Simple debounce utility
function debounce<T extends (...args: any[]) => void>(func: T, wait: number): (...args: Parameters<T>) => void {
    let timeout: ReturnType<typeof setTimeout> | null = null;
    return (...args: Parameters<T>) => {
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

const Subjects: React.FC = () => {
    const [data, setData] = useState<AccountTreeNode[]>([]);
    const [loading, setLoading] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [editingSubject, setEditingSubject] = useState<Account | null>(null);
    const [form] = Form.useForm();
    const [searchText, setSearchText] = useState('');
    const [expandedKeys, setExpandedKeys] = useState<React.Key[]>([]);
    const { currentBook } = useBook();

    const fetchSubjects = useCallback(async () => {
        if (!currentBook) return;
        setLoading(true);
        try {
            const response = await accountsApi.list();
            const list: Account[] = response.data;

            // Build tree structure using parent_id
            const map: { [key: number]: AccountTreeNode } = {};
            const tree: AccountTreeNode[] = [];

            list.forEach(item => {
                map[item.id] = { ...item, children: [] };
            });

            list.forEach(item => {
                if (item.parent_id && map[item.parent_id]) {
                    map[item.parent_id].children?.push(map[item.id]);
                } else {
                    tree.push(map[item.id]);
                }
            });

            setData(tree);
            // By default, only expand top level
            setExpandedKeys(tree.map(item => item.id));
        } catch (error: unknown) {
            console.error('Failed to load accounts:', error);
            message.error('加载科目失败');
        } finally {
            setLoading(false);
        }
    }, [currentBook]);

    useEffect(() => {
        fetchSubjects();
    }, [fetchSubjects]);

    // Debounced search handler
    const debouncedSearch = useCallback(
        debounce((value: string) => {
            setSearchText(value);
        }, 300),
        []
    );

    const handleAdd = () => {
        if (!currentBook) {
            message.warning('请先选择账套');
            return;
        }
        setEditingSubject(null);
        form.resetFields();
        setIsModalOpen(true);
    };

    const handleEdit = (record: Account) => {
        setEditingSubject(record);
        form.setFieldsValue({
            name: record.name,
            is_active: record.is_active
        });
        setIsModalOpen(true);
    };

    const handleDelete = async (id: number) => {
        try {
            await accountsApi.deactivate(id);
            message.success('科目已停用');
            fetchSubjects();
        } catch (error: unknown) {
            console.error('Failed to deactivate account:', error);
            message.error('停用失败');
        }
    };

    const handleOk = async () => {
        try {
            const values = await form.validateFields();

            if (editingSubject) {
                // Update existing account
                const updateData: UpdateAccountRequest = {
                    name: values.name,
                    is_active: values.is_active
                };
                await accountsApi.update(editingSubject.id, updateData);
                message.success('科目更新成功');
            } else {
                // Create new account
                const createData: CreateAccountRequest = {
                    code: values.code,
                    name: values.name,
                    parent_id: values.parent_id || undefined,
                    element_type: values.element_type,
                    balance_direction: values.balance_direction,
                    has_aux: values.has_aux || false
                };
                await accountsApi.create(createData);
                message.success('科目创建成功');
            }

            setIsModalOpen(false);
            fetchSubjects();
        } catch (error: unknown) {
            console.error('Failed to save account:', error);
            // Only show error message for non-validation errors
            if (error && typeof error === 'object' && 'response' in error) {
                message.error(editingSubject ? '更新失败' : '创建失败');
            }
        }
    };

    const columns: TableProps<AccountTreeNode>['columns'] = [
        {
            title: '科目编码',
            dataIndex: 'code',
            key: 'code',
            sorter: (a, b) => a.code.localeCompare(b.code),
            width: 150,
            render: (text) => <span className="font-mono font-medium text-slate-300">{text}</span>
        },
        {
            title: '科目名称',
            dataIndex: 'name',
            key: 'name',
            render: (text) => <span className="font-medium text-slate-200">{text}</span>
        },
        {
            title: '类别',
            dataIndex: 'element_type',
            key: 'element_type',
            render: (type: string) => <Tag color="blue" bordered={false}>{ELEMENT_TYPE_LABELS[type] || type}</Tag>,
        },
        {
            title: '余额方向',
            dataIndex: 'balance_direction',
            key: 'balance_direction',
            render: (direction: string) => (
                <Tag color={direction === 'debit' ? 'green' : 'orange'} bordered={false}>
                    {BALANCE_DIRECTION_LABELS[direction] || direction}
                </Tag>
            ),
        },
        {
            title: '辅助核算',
            dataIndex: 'has_aux',
            key: 'has_aux',
            render: (hasAux: boolean) => (
                hasAux ? <Tag color="cyan" bordered={false}>启用</Tag> : <span className="text-slate-500">-</span>
            ),
        },
        {
            title: '状态',
            dataIndex: 'is_active',
            key: 'is_active',
            render: (isActive: boolean) => (
                <Tag color={isActive ? 'green' : 'red'} bordered={false}>
                    {isActive ? '启用' : '停用'}
                </Tag>
            ),
        },
        {
            title: '操作',
            key: 'action',
            width: 120,
            align: 'center',
            render: (_, record) => (
                <Space size="small">
                    <Tooltip title="编辑">
                        <Button
                            type="text"
                            shape="circle"
                            icon={<EditOutlined />}
                            className="text-slate-400 hover:text-blue-400 hover:bg-slate-700/50"
                            onClick={() => handleEdit(record)}
                        />
                    </Tooltip>
                    <Popconfirm title="确定要停用吗？" onConfirm={() => handleDelete(record.id)}>
                        <Tooltip title="停用">
                            <Button type="text" shape="circle" icon={<DeleteOutlined />} className="text-slate-400 hover:text-red-400 hover:bg-slate-700/50" />
                        </Tooltip>
                    </Popconfirm>
                </Space>
            ),
        },
    ];

    // Recursive search filter
    const getFilteredData = (list: AccountTreeNode[]): AccountTreeNode[] => {
        if (!searchText) return list;

        return list.reduce((acc: AccountTreeNode[], item) => {
            const matches = item.code.toLowerCase().includes(searchText.toLowerCase()) ||
                item.name.toLowerCase().includes(searchText.toLowerCase());

            const filteredChildren = item.children ? getFilteredData(item.children) : [];

            if (matches || filteredChildren.length > 0) {
                acc.push({ ...item, children: filteredChildren });
            }
            return acc;
        }, []);
    };

    const filteredData = getFilteredData(data);

    const handleExpandAll = () => {
        const keys: React.Key[] = [];
        const traverse = (list: AccountTreeNode[]) => {
            list.forEach(item => {
                keys.push(item.id);
                if (item.children) traverse(item.children);
            });
        };
        traverse(data);
        setExpandedKeys(keys);
    };

    const handleCollapseAll = () => {
        setExpandedKeys([]);
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-slate-200">会计科目</h2>
                    <p className="text-slate-400">管理会计科目体系及属性</p>
                </div>
                <Button
                    type="primary"
                    icon={<PlusOutlined />}
                    size="large"
                    className="shadow-md shadow-amber-500/20"
                    onClick={handleAdd}
                >
                    新增科目
                </Button>
            </div>

            <div className="flex flex-wrap gap-4 items-center">
                <Input
                    placeholder="搜索科目编码或名称..."
                    prefix={<SearchOutlined className="text-slate-400" />}
                    allowClear
                    className="w-80 glass-panel border-slate-700 bg-slate-800/30 text-slate-200"
                    onChange={e => debouncedSearch(e.target.value)}
                />
                <Space>
                    <Button icon={<VerticalAlignBottomOutlined />} onClick={handleExpandAll}>展开全部</Button>
                    <Button icon={<VerticalAlignTopOutlined />} onClick={handleCollapseAll}>折叠全部</Button>
                </Space>
            </div>

            <Card bordered={false} className="glass-panel rounded-xl overflow-hidden">
                <Table<AccountTreeNode>
                    columns={columns}
                    dataSource={filteredData}
                    loading={loading}
                    rowKey="id"
                    pagination={{
                        pageSize: 50,
                        showSizeChanger: true,
                        showTotal: (total) => `共 ${total} 个项目`
                    }}
                    expandable={{
                        expandedRowKeys: expandedKeys,
                        onExpandedRowsChange: (keys) => setExpandedKeys(keys as React.Key[])
                    }}
                    scroll={{ y: 'calc(100vh - 380px)' }}
                    className="custom-table"
                />
            </Card>

            <Modal
                title={editingSubject ? "编辑科目" : "新增科目"}
                open={isModalOpen}
                onOk={handleOk}
                onCancel={() => setIsModalOpen(false)}
                destroyOnClose
            >
                <Form form={form} layout="vertical">
                    {!editingSubject && (
                        <>
                            <Form.Item name="code" label="科目编码" rules={[{ required: true, message: '请输入科目编码' }]}>
                                <Input placeholder="例如: 1001" />
                            </Form.Item>
                            <Form.Item name="parent_id" label="上级科目">
                                <Select placeholder="选择上级科目 (可选)" allowClear showSearch>
                                    {(() => {
                                        const flatList: AccountTreeNode[] = [];
                                        const traverse = (list: AccountTreeNode[]) => {
                                            list.forEach(item => {
                                                flatList.push(item);
                                                if (item.children) traverse(item.children);
                                            });
                                        };
                                        traverse(data);
                                        return flatList.map(s => (
                                            <Select.Option key={s.id} value={s.id}>{s.code} {s.name}</Select.Option>
                                        ));
                                    })()}
                                </Select>
                            </Form.Item>
                            <Form.Item name="element_type" label="科目类别" rules={[{ required: true, message: '请选择类别' }]}>
                                <Select placeholder="选择科目类别">
                                    <Select.Option value="asset">资产</Select.Option>
                                    <Select.Option value="liability">负债</Select.Option>
                                    <Select.Option value="equity">权益</Select.Option>
                                    <Select.Option value="income">收入</Select.Option>
                                    <Select.Option value="expense">费用</Select.Option>
                                    <Select.Option value="cost">成本</Select.Option>
                                </Select>
                            </Form.Item>
                            <Form.Item name="balance_direction" label="余额方向" rules={[{ required: true, message: '请选择余额方向' }]}>
                                <Radio.Group>
                                    <Radio value="debit">借方</Radio>
                                    <Radio value="credit">贷方</Radio>
                                </Radio.Group>
                            </Form.Item>
                            <Form.Item name="has_aux" label="启用辅助核算" valuePropName="checked">
                                <Radio.Group>
                                    <Radio value={true}>启用</Radio>
                                    <Radio value={false}>不启用</Radio>
                                </Radio.Group>
                            </Form.Item>
                        </>
                    )}
                    <Form.Item name="name" label="科目名称" rules={[{ required: true, message: '请输入科目名称' }]}>
                        <Input placeholder="例如: 库存现金" />
                    </Form.Item>
                    {editingSubject && (
                        <Form.Item name="is_active" label="状态" valuePropName="checked">
                            <Radio.Group>
                                <Radio value={true}>启用</Radio>
                                <Radio value={false}>停用</Radio>
                            </Radio.Group>
                        </Form.Item>
                    )}
                </Form>
            </Modal>
        </div>
    );
};

export default Subjects;
