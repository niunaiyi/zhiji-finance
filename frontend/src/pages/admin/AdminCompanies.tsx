import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Tag, Modal, Form, Input, InputNumber, Space, message, Popconfirm, Drawer } from 'antd';
import { PlusOutlined, StopOutlined, CheckCircleOutlined, TeamOutlined, UserAddOutlined } from '@ant-design/icons';
import apiClient from '../../api/client';

const AdminCompanies: React.FC = () => {
    const [companies, setCompanies] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [createOpen, setCreateOpen] = useState(false);
    const [adminOpen, setAdminOpen] = useState(false);
    const [membersOpen, setMembersOpen] = useState(false);
    const [members, setMembers] = useState<any[]>([]);
    const [selectedCompany, setSelectedCompany] = useState<any>(null);
    const [form] = Form.useForm();
    const [adminForm] = Form.useForm();

    const fetchCompanies = () => {
        setLoading(true);
        apiClient.get('/v1/admin/companies')
            .then(res => setCompanies(res.data.data ?? []))
            .catch(() => message.error('加载失败'))
            .finally(() => setLoading(false));
    };

    useEffect(() => { fetchCompanies(); }, []);

    const handleCreate = () => {
        form.validateFields().then(values => {
            apiClient.post('/v1/auth/companies', values)
                .then(() => { message.success('账套创建成功'); setCreateOpen(false); fetchCompanies(); })
                .catch(err => message.error(err.response?.data?.message ?? '创建失败'));
        });
    };

    const handleCreateAdmin = () => {
        adminForm.validateFields().then(values => {
            apiClient.post(`/v1/admin/companies/${selectedCompany.id}/admins`, values)
                .then(() => { 
                    message.success('管理员创建成功'); 
                    setAdminOpen(false); 
                    if (membersOpen) viewMembers(selectedCompany);
                    fetchCompanies();
                })
                .catch(err => message.error(err.response?.data?.message ?? '创建失败'));
        });
    };

    const toggleStatus = (company: any) => {
        const next = company.status === 'active' ? 'suspended' : 'active';
        apiClient.patch(`/v1/admin/companies/${company.id}/status`, { status: next })
            .then(() => { message.success('状态已更新'); fetchCompanies(); })
            .catch(() => message.error('操作失败'));
    };

    const viewMembers = (company: any) => {
        setSelectedCompany(company);
        apiClient.get(`/v1/admin/companies/${company.id}/users`)
            .then(res => { setMembers(res.data.data ?? []); setMembersOpen(true); })
            .catch(() => message.error('加载成员失败'));
    };

    const columns = [
        { title: 'ID', dataIndex: 'id', key: 'id', width: 60 },
        { title: '编码', dataIndex: 'code', key: 'code', render: (v: string) => <span className="font-mono text-violet-400 font-bold">{v}</span> },
        { title: '名称', dataIndex: 'name', key: 'name', render: (v: string) => <span className="font-medium text-slate-200">{v}</span> },
        { title: '会计年度起始月', dataIndex: 'fiscal_year_start', key: 'fiscal_year_start', render: (v: number) => `第 ${v} 月` },
        { title: '用户数', dataIndex: 'user_count', key: 'user_count' },
        {
            title: '状态', dataIndex: 'status', key: 'status',
            render: (v: string) => <Tag color={v === 'active' ? 'green' : 'red'}>{v === 'active' ? '正常运行' : '已停用'}</Tag>
        },
        {
            title: '操作', key: 'action',
            render: (_: any, record: any) => (
                <Space>
                    <Button size="small" icon={<TeamOutlined />} onClick={() => viewMembers(record)}>管理成员</Button>
                    <Popconfirm
                        title={record.status === 'active' ? '确定停用此账套？' : '确定启用此账套？'}
                        onConfirm={() => toggleStatus(record)}
                    >
                        <Button
                            size="small"
                            danger={record.status === 'active'}
                            icon={record.status === 'active' ? <StopOutlined /> : <CheckCircleOutlined />}
                        >
                            {record.status === 'active' ? '停用' : '启用'}
                        </Button>
                    </Popconfirm>
                </Space>
            )
        },
    ];

    const memberColumns = [
        { title: '姓名', dataIndex: 'name', key: 'name' },
        { title: '邮箱', dataIndex: 'email', key: 'email', render: (v: string) => <span className="font-mono text-slate-400 text-xs">{v}</span> },
        { title: '角色', dataIndex: 'role', key: 'role', render: (v: string) => <Tag color={v === 'admin' ? 'purple' : 'default'}>{v}</Tag> },
    ];

    return (
        <div className="space-y-6 animate-fade-in">
            <div className="flex justify-between items-center">
                <div>
                    <h2 className="text-3xl font-bold text-white">账套管理</h2>
                    <p className="text-slate-400 mt-1">管理所有公司账套及其管理员</p>
                </div>
                <Button type="primary" icon={<PlusOutlined />} size="large" onClick={() => { form.resetFields(); setCreateOpen(true); }}>
                    新建账套
                </Button>
            </div>

            <Card variant="borderless" className="glass-panel rounded-2xl">
                <Table
                    dataSource={companies}
                    columns={columns}
                    loading={loading}
                    rowKey="id"
                    pagination={{ pageSize: 15 }}
                />
            </Card>

            {/* Create Company Modal */}
            <Modal title="新建账套" open={createOpen} onOk={handleCreate} onCancel={() => setCreateOpen(false)} destroyOnClose>
                <Form form={form} layout="vertical" className="mt-4">
                    <Form.Item name="code" label="账套编码" rules={[{ required: true, message: '请输入唯一编码' }, { max: 20 }]}>
                        <Input placeholder="如 ZHIJI_2026" style={{ textTransform: 'uppercase' }} />
                    </Form.Item>
                    <Form.Item name="name" label="账套名称" rules={[{ required: true }]}>
                        <Input placeholder="如 知积科技有限公司" />
                    </Form.Item>
                    <Form.Item name="fiscal_year_start" label="会计年度起始月" initialValue={1}>
                        <InputNumber min={1} max={12} style={{ width: '100%' }} addonAfter="月" />
                    </Form.Item>
                </Form>
            </Modal>

            {/* Members Drawer */}
            <Drawer
                title={`${selectedCompany?.name} — 成员管理`}
                open={membersOpen}
                onClose={() => setMembersOpen(false)}
                width={560}
                extra={
                    <Button type="primary" icon={<UserAddOutlined />} onClick={() => { adminForm.resetFields(); setAdminOpen(true); }}>
                        添加管理员
                    </Button>
                }
            >
                <Table dataSource={members} columns={memberColumns} rowKey="id" pagination={false} size="small" />
            </Drawer>

            {/* Create Admin Modal */}
            <Modal title={`为 [${selectedCompany?.name}] 添加管理员`} open={adminOpen} onOk={handleCreateAdmin} onCancel={() => setAdminOpen(false)} destroyOnClose>
                <p className="text-slate-400 mb-4 text-xs">新建的用户将自动获得该账套的 Admin 角色权限。</p>
                <Form form={adminForm} layout="vertical">
                    <Form.Item name="name" label="管理员姓名" rules={[{ required: true }]}>
                        <Input placeholder="张三" />
                    </Form.Item>
                    <Form.Item name="email" label="登录邮箱" rules={[{ required: true, type: 'email' }]}>
                        <Input placeholder="admin@company.com" />
                    </Form.Item>
                    <Form.Item name="password" label="初始密码" rules={[{ required: true, min: 6 }]}>
                        <Input.Password placeholder="至少6位" />
                    </Form.Item>
                </Form>
            </Modal>
        </div>
    );
};

export default AdminCompanies;
