import React from 'react';
import { Layout, Menu, Button, Avatar, Tooltip } from 'antd';
import {
    BankOutlined,
    LogoutOutlined,
    CrownOutlined,
} from '@ant-design/icons';
import { Outlet, useNavigate, useLocation, Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const { Sider, Content, Header } = Layout;

const AdminLayout: React.FC = () => {
    const navigate = useNavigate();
    const location = useLocation();
    const { logout, user } = useAuth();

    // Strict isolation: Only SuperAdmins can access AdminLayout
    if (user && !user.is_super_admin) {
        return <Navigate to="/" replace />;
    }

    const handleLogout = () => {
        logout();
        navigate('/login');
    };

    const menuItems = [
        { key: '/admin/companies', icon: <BankOutlined />, label: '账套管理' },
    ];

    const selectedKey = menuItems.find(m => m.key === location.pathname)?.key ?? '/admin/companies';

    return (
        <Layout style={{ minHeight: '100vh' }}>
            {/* Sidebar */}
            <Sider
                width={240}
                style={{
                    background: '#0A0F1E',
                    borderRight: '1px solid rgba(255,255,255,0.06)',
                    boxShadow: '4px 0 24px rgba(0,0,0,0.4)',
                    position: 'fixed',
                    left: 0,
                    top: 0,
                    bottom: 0,
                    zIndex: 100,
                }}
            >
                {/* Logo */}
                <div className="flex items-center gap-3 px-6 py-5 border-b border-white/5">
                    <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-purple-700 flex items-center justify-center shadow-lg shadow-violet-500/30">
                        <CrownOutlined style={{ color: '#fff', fontSize: 18 }} />
                    </div>
                    <div>
                        <p className="text-white font-bold text-base leading-none font-sans">SuperAdmin</p>
                        <p className="text-violet-400 text-xs mt-0.5">系统管理控制台</p>
                    </div>
                </div>

                {/* Navigation */}
                <Menu
                    theme="dark"
                    mode="inline"
                    selectedKeys={[selectedKey]}
                    onClick={({ key }) => navigate(key)}
                    style={{
                        background: 'transparent',
                        borderRight: 0,
                        padding: '16px 12px',
                        fontSize: '14px',
                    }}
                    items={menuItems}
                />

                {/* User profile + logout */}
                <div
                    style={{ position: 'absolute', bottom: 0, left: 0, right: 0 }}
                    className="p-4 border-t border-white/5 flex items-center justify-between"
                >
                    <div className="flex items-center gap-2 min-w-0">
                        <Avatar
                            size={32}
                            style={{ background: 'linear-gradient(135deg, #8B5CF6, #7C3AED)', flexShrink: 0 }}
                        >
                            {user?.name?.[0] ?? 'A'}
                        </Avatar>
                        <div className="min-w-0">
                            <p className="text-slate-200 text-sm font-medium truncate leading-none">{user?.name ?? 'Admin'}</p>
                            <p className="text-slate-500 text-xs truncate mt-0.5">{user?.email}</p>
                        </div>
                    </div>
                    <Tooltip title="退出登录">
                        <Button
                            type="text"
                            danger
                            icon={<LogoutOutlined />}
                            onClick={handleLogout}
                            size="small"
                        />
                    </Tooltip>
                </div>
            </Sider>

            {/* Main area */}
            <Layout style={{ marginLeft: 240, background: '#0F172A' }}>
                <Header
                    style={{
                        background: 'rgba(15,23,42,0.8)',
                        backdropFilter: 'blur(12px)',
                        borderBottom: '1px solid rgba(255,255,255,0.05)',
                        padding: '0 32px',
                        display: 'flex',
                        alignItems: 'center',
                        height: 64,
                        position: 'sticky',
                        top: 0,
                        zIndex: 50,
                    }}
                >
                    <span className="text-slate-400 text-sm">
                        {menuItems.find(m => m.key === selectedKey)?.label ?? '系统管理'}
                    </span>
                    <span className="mx-2 text-slate-700">/</span>
                    <span className="text-slate-200 text-sm font-medium">
                        {menuItems.find(m => m.key === selectedKey)?.label ?? 'Overview'}
                    </span>
                </Header>
                <Content style={{ padding: '32px', minHeight: 'calc(100vh - 64px)' }}>
                    <Outlet />
                </Content>
            </Layout>
        </Layout>
    );
};

export default AdminLayout;
