import React, { useState } from 'react';
import { Layout, Menu, Button, theme, Dropdown, Avatar, Space, Badge, Tabs, Tooltip } from 'antd';
import { useBook } from '../context/BookContext';
import { useTabs } from '../context/TabContext';
import { useSettings } from '../context/SettingsContext';
import {
    MenuFoldOutlined,
    MenuUnfoldOutlined,
    DashboardOutlined,
    BookOutlined,
    FileTextOutlined,
    SettingOutlined,
    UserOutlined,
    LogoutOutlined,
    BellOutlined,
    CalendarOutlined,
    AppstoreOutlined,
    SolutionOutlined,
    ShopOutlined,
    TransactionOutlined,
    PieChartOutlined,
    ColumnHeightOutlined
} from '@ant-design/icons';
import { Outlet, Navigate, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

const { Header, Sider, Content, Footer } = Layout;

const MainLayout: React.FC = () => {
    const [collapsed, setCollapsed] = useState(false);
    const { setTableSize } = useSettings();
    const { currentBook } = useBook();
    const { user, logout } = useAuth();
    const navigate = useNavigate();
    const { tabs, activeKey, openTab, removeTab, setActiveKey } = useTabs();
    
    // Strict isolation: SuperAdmins CANNOT access MainLayout
    if (user?.is_super_admin) {
        return <Navigate to="/admin" replace />;
    }

    const {
        token: { colorBgLayout },
    } = theme.useToken();

    const densityMenu = {
        items: [
            { key: 'default', label: '默认间距', onClick: () => setTableSize('default') },
            { key: 'middle', label: '中等间距', onClick: () => setTableSize('middle') },
            { key: 'small', label: '紧凑间距', onClick: () => setTableSize('small') },
        ]
    };

    const userMenu = {
        items: [
            {
                key: 'profile',
                label: '个人中心',
                icon: <UserOutlined />,
            },
            {
                key: 'logout',
                label: '退出登录',
                icon: <LogoutOutlined />,
                danger: true,
                onClick: () => {
                    logout();
                    navigate('/login');
                }
            },
        ]
    };

    const menuItems = [
        {
            key: '/',
            icon: <DashboardOutlined style={{ fontSize: '18px' }} />,
            label: '控制面板',
        },
        {
            key: 'accounting',
            icon: <FileTextOutlined style={{ fontSize: '18px' }} />,
            label: '账务中心',
            children: [
                { key: '/vouchers', icon: <TransactionOutlined />, label: '凭证处理' },
                { key: '/subjects', icon: <BookOutlined />, label: '科目档案' },
            ]
        },
        {
            key: '/entities',
            icon: <SolutionOutlined style={{ fontSize: '18px' }} />,
            label: '辅助核算管理',
        },
        {
            key: 'receivables_group',
            icon: <TransactionOutlined style={{ fontSize: '18px' }} />,
            label: '往来管理',
            children: [
                { key: '/receivables', label: '应收管理' },
                { key: '/payables', label: '应付管理' },
                { key: '/reports/aging', label: '账龄分析' },
            ]
        },
        {
            key: 'business_group',
            icon: <TransactionOutlined style={{ fontSize: '18px' }} />,
            label: '业务管理',
            children: [
                { key: '/purchase', label: '采购管理' },
                { key: '/sales', label: '销售管理' },
            ]
        },
        {
            key: 'inventory_mgmt',
            icon: <ShopOutlined style={{ fontSize: '18px' }} />,
            label: '存货管理',
            children: [
                { key: '/inventory', label: '存货档案' },
                { key: '/reports/inventory', label: '库存余额' },
            ]
        },
        {
            key: '/assets',
            icon: <AppstoreOutlined style={{ fontSize: '18px' }} />,
            label: '固定资产',
        },
        {
            key: '/payroll',
            icon: <SolutionOutlined style={{ fontSize: '18px' }} />,
            label: '薪酬管理',
        },
        {
            key: 'reports_group',
            icon: <PieChartOutlined style={{ fontSize: '18px' }} />,
            label: '报表中心',
            children: [
                { key: '/reports', label: '财务报表' },
                { key: '/reports/cash-flow', label: '现金流量表' },
                { key: '/reports/balance-sheet', label: '资产负债表' },
                { key: '/reports/detail-ledger', label: '明细账' },
                { key: '/reports/chronological', label: '序时账' },
                { key: '/reports/auxiliary-ledger', label: '辅助核算账' },
            ]
        },
        {
            key: '/period-end',
            icon: <CalendarOutlined style={{ fontSize: '18px' }} />,
            label: '会计期间',
        },
        {
            key: 'system_settings',
            icon: <SettingOutlined style={{ fontSize: '18px' }} />,
            label: '系统设置',
            children: [
                { key: '/settings', label: '数据字典管理' },
            ]
        },
    ];

    return (
        <Layout style={{ height: '100vh', overflow: 'hidden' }}>
            <Sider
                trigger={null}
                collapsible
                collapsed={collapsed}
                width={260}
                style={{
                    background: '#0F172A', // Slate 900
                    borderRight: '1px solid rgba(255,255,255,0.05)',
                    boxShadow: '4px 0 24px 0 rgba(0, 0, 0, 0.2)',
                    zIndex: 20,
                }}
            >
                <div className="flex items-center justify-center h-20 border-b border-gray-800/50 mx-4 mb-2">
                    <div className={`flex items-center gap-3 transition-all duration-300 ${collapsed ? 'scale-0 w-0 opacity-0' : 'scale-100 opacity-100'}`}>
                        <div className="w-9 h-9 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-amber-500/20">F</div>
                        <h1 className="text-xl font-bold text-white tracking-wide font-sans">
                            智积财务
                        </h1>
                    </div>
                    {collapsed && (
                        <div className="w-9 h-9 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-amber-500/20">F</div>
                    )}
                </div>
                <Menu
                    theme="dark"
                    mode="inline"
                    selectedKeys={[activeKey]}
                    onClick={({ key }) => {
                        // Find the menu item to get the label
                        const findLabel = (items: any[], k: string): string => {
                            for (const item of items) {
                                if (item.key === k) return item.label as string;
                                if (item.children) {
                                    const l = findLabel(item.children, k);
                                    if (l) return l;
                                }
                            }
                            return '';
                        };
                        const label = findLabel(menuItems, key as string);

                        // key is the path here because we set it so in menuItems
                        openTab(key as string, label, key as string);
                    }}
                    style={{
                        background: 'transparent',
                        borderRight: 0,
                        padding: '0 12px',
                        fontSize: '15px'
                    }}
                    items={menuItems}
                />
            </Sider>
            <Layout style={{ background: colorBgLayout }}>
                <Header
                    style={{
                        padding: '0 32px',
                        background: 'rgba(30, 41, 59, 0.7)', // Slate 800 with opacity
                        backdropFilter: 'blur(12px)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        borderBottom: '1px solid rgba(255,255,255,0.05)',
                        zIndex: 19,
                        height: 72,
                        position: 'sticky',
                        top: 0
                    }}
                >
                    <div className="flex items-center gap-4">
                        <Button
                            type="text"
                            icon={collapsed ? <MenuUnfoldOutlined /> : <MenuFoldOutlined />}
                            onClick={() => setCollapsed(!collapsed)}
                            style={{
                                fontSize: '16px',
                                width: 40,
                                height: 40,
                                borderRadius: '10px',
                                color: '#94A3B8' // Slate 400
                            }}
                        />

                        <div className="flex items-center gap-3 ml-4 bg-slate-800/40 p-1 rounded-lg border border-slate-700/50 px-3">
                             <div className="flex items-center gap-2">
                                <span className="text-slate-400 text-xs">当前账套:</span>
                                <span className="text-slate-200 font-medium">{currentBook?.name || '未选择'}</span>
                             </div>
                        </div>
                    </div>

                    <Space size="large">
                        <Tooltip title="调整表格间距">
                            <Dropdown menu={densityMenu} placement="bottomRight">
                                <Button type="text" shape="circle" icon={<ColumnHeightOutlined />} style={{ color: '#94A3B8' }} />
                            </Dropdown>
                        </Tooltip>

                        <Badge count={3} size="small" offset={[-5, 5]}>
                            <Button type="text" shape="circle" icon={<BellOutlined />} style={{ color: '#94A3B8' }} />
                        </Badge>
                        <Dropdown menu={userMenu} placement="bottomRight" arrow={{ pointAtCenter: true }}>
                            <div className="flex items-center cursor-pointer gap-3 hover:bg-slate-800/50 pl-2 pr-4 py-1.5 rounded-full transition-all border border-transparent hover:border-slate-700">
                                <Avatar
                                    style={{
                                        backgroundColor: '#8B5CF6',
                                        background: 'linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%)', // Violet
                                        boxShadow: '0 4px 6px -1px rgba(139, 92, 246, 0.3)'
                                    }}
                                    icon={<UserOutlined />}
                                />
                                <div className="flex flex-col items-start leading-none">
                                    <span className="text-slate-200 font-semibold text-sm">{user?.name || '管理员'}</span>
                                    <span className="text-slate-500 text-xs">{user?.is_super_admin ? '系统管理员' : '账套成员'}</span>
                                </div>
                            </div>
                        </Dropdown>
                    </Space>
                </Header>
                <Content
                    style={{
                        margin: '16px 24px 0',
                        display: 'flex',
                        flexDirection: 'column',
                        height: 'calc(100vh - 72px)', // Adjust for header height
                        overflow: 'hidden',
                    }}
                >
                    <div className="flex-none bg-inherit">
                        <Tabs
                            hideAdd
                            type="editable-card"
                            activeKey={activeKey}
                            onChange={setActiveKey}
                            onEdit={(targetKey, action) => {
                                if (action === 'remove') removeTab(targetKey as string);
                            }}
                            className="custom-tabs"
                            items={tabs.map(tab => ({
                                key: tab.key,
                                label: tab.label,
                                closable: tab.closable !== false
                            }))}
                        />
                    </div>
                    <div className="flex-auto overflow-y-auto mt-2 pr-2 pb-6">
                        <Outlet />
                    </div>
                </Content>
                <Footer style={{ textAlign: 'center', color: '#475569', background: 'transparent', padding: '0 0 24px' }}>
                    智积财务管理系统 ©{new Date().getFullYear()}
                </Footer>
            </Layout>
        </Layout>
    );
};

export default MainLayout;
