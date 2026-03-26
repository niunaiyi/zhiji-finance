import React, { useEffect, useState } from 'react';
import { Card, Statistic, Row, Col, List, Tag, Empty, Tooltip } from 'antd';
import {
    ArrowUpOutlined,
    FileTextOutlined,
    ClockCircleOutlined,
    AreaChartOutlined
} from '@ant-design/icons';
import apiClient from '../api/client';
import { useBook } from '../context/BookContext';
import dayjs from 'dayjs';

const Dashboard: React.FC = () => {
    const { currentBook } = useBook();
    const [stats, setStats] = useState<any>(null);
    const [loading, setLoading] = useState(true);

    const fetchStats = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get(`/dashboard/stats?book_code=${currentBook.code}`)
            .then(res => {
                setStats(res.data.data);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    };

    useEffect(() => {
        fetchStats();
    }, [currentBook]);

    const statCards = [
        {
            title: "总账套数",
            value: stats?.total_books || 0,
            prefix: <ArrowUpOutlined />,
            trend: "已同步",
            icon: <BookIcon className="w-8 h-8" />,
            color: "#10B981"
        },
        {
            title: "本月凭证数",
            value: stats?.monthly_vouchers || 0,
            prefix: <FileTextOutlined />,
            trend: "本月",
            icon: <VoucherIcon className="w-8 h-8" />,
            color: "#3B82F6"
        },
        {
            title: "待审核凭证",
            value: stats?.pending_vouchers || 0,
            prefix: <ClockCircleOutlined />,
            trend: "待处理",
            icon: <AuditIcon className="w-8 h-8" />,
            color: "#F59E0B"
        }
    ];

    return (
        <div className="space-y-8 animate-fade-in text-slate-200">
            <div className="mb-8 flex justify-between items-end">
                <div>
                    <h2 className="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-amber-400 to-amber-600 tracking-tight font-sans">工作台</h2>
                    <p className="text-slate-400 mt-1">欢迎回来。这里是 {currentBook?.name} 的财务概览。</p>
                </div>
                <div className="text-right hidden sm:block">
                    <p className="text-sm text-slate-500 font-mono">{dayjs().format('YYYY年MM月DD日')} {['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'][dayjs().day()]}</p>
                </div>
            </div>

            <Row gutter={[24, 24]}>
                {statCards.map((item, index) => (
                    <Col xs={24} sm={8} key={index}>
                        <Card
                            variant="borderless"
                            loading={loading}
                            className="glass-panel hover-card rounded-2xl overflow-hidden shadow-lg relative group"
                            styles={{ body: { padding: '28px' } }}
                        >
                            <div className="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                            <div className="flex justify-between items-start relative z-10">
                                <div>
                                    <p className="text-slate-400 mb-2 font-medium text-sm tracking-wide uppercase">{item.title}</p>
                                    <Statistic
                                        value={item.value}
                                        valueStyle={{ fontSize: '42px', fontWeight: 700, color: '#F8FAFC', letterSpacing: '-0.03em', fontFamily: 'IBM Plex Sans' }}
                                        prefix={item.prefix}
                                    />
                                    <div className="mt-3 text-sm flex items-center">
                                        <span className={`${item.color === '#10B981' ? 'text-emerald-400 bg-emerald-500/10' : item.color === '#F59E0B' ? 'text-amber-400 bg-amber-500/10' : 'text-blue-400 bg-blue-500/10'} px-2.5 py-0.5 rounded-full font-medium text-xs`}>
                                            {item.trend}
                                        </span>
                                    </div>
                                </div>
                                <div className={`p-4 rounded-xl bg-slate-800/50 border border-white/5 shadow-inner text-slate-200 group-hover:scale-110 transition-transform duration-300`}>
                                    {item.icon}
                                </div>
                            </div>
                        </Card>
                    </Col>
                ))}
            </Row>

            <Row gutter={[24, 24]}>
                <Col xs={24} lg={16}>
                    <Card
                        title={<span className="text-lg font-bold text-slate-200">最近凭证</span>}
                        variant="borderless"
                        loading={loading}
                        className="glass-panel rounded-2xl h-full shadow-lg"
                        extra={<a href="/vouchers" className="text-amber-500 hover:text-amber-400 font-medium px-3 py-1 rounded-lg hover:bg-amber-500/10 transition-colors text-sm">查看全部 &rarr;</a>}
                        styles={{ header: { borderBottom: '1px solid rgba(255,255,255,0.05)', padding: '0 24px', minHeight: '60px' }, body: { padding: '0 24px 24px' } }}
                    >
                        <List
                            itemLayout="horizontal"
                            dataSource={stats?.recent_vouchers || []}
                            locale={{ emptyText: <Empty description="暂无凭证" image={Empty.PRESENTED_IMAGE_SIMPLE} /> }}
                            renderItem={(item: any) => (
                                <List.Item
                                    className="hover:bg-slate-800/30 transition-colors rounded-lg px-3 -mx-3 my-1 border-b border-white/5 last:border-0"
                                    style={{ padding: '16px 12px' }}
                                >
                                    <List.Item.Meta
                                        avatar={
                                            <div className="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 border border-white/5">
                                                <FileTextOutlined style={{ fontSize: '18px' }} />
                                            </div>
                                        }
                                        title={<span className="font-semibold text-slate-200">记账凭证 #{String(item.voucher_number).padStart(3, '0')}</span>}
                                        description={
                                            <div className="flex items-center gap-2 mt-1">
                                                <span className="text-slate-500 text-xs font-mono">{item.entries?.[0]?.summary || '无摘要'} - {item.voucher_date}</span>
                                            </div>
                                        }
                                    />
                                    <div className="flex flex-col items-end mr-4">
                                        <span className="font-bold text-slate-200 font-mono tracking-tight">
                                            ¥ {(item.entries?.reduce((sum: number, e: any) => sum + Number(e.debit_amount), 0) || 0).toLocaleString()}
                                        </span>
                                        <Tag color="#10B981" bordered={false} className="mr-0 mt-1 bg-opacity-10 border-0" style={{ backgroundColor: 'rgba(16, 185, 129, 0.1)', color: '#34D399' }}>
                                            已记账
                                        </Tag>
                                    </div>
                                </List.Item>
                            )}
                        />
                    </Card>
                </Col>
                <Col xs={24} lg={8}>
                    <Card
                        title={<div className="flex items-center gap-2"><AreaChartOutlined /><span className="text-lg font-bold text-slate-200">月度交易趋势</span></div>}
                        variant="borderless"
                        loading={loading}
                        className="glass-panel rounded-2xl h-full shadow-lg"
                        styles={{ header: { borderBottom: '1px solid rgba(255,255,255,0.05)', padding: '0 24px', minHeight: '60px' } }}
                    >
                        <div className="h-64 flex items-end justify-around pb-4">
                            {stats?.chart_data?.map((d: any, i: number) => {
                                const max = Math.max(...(stats?.chart_data?.map((x: any) => x.amount) || [1])) || 1;
                                const height = (d.amount / max) * 100;
                                return (
                                    <div key={i} className="flex flex-col items-center flex-1">
                                        <Tooltip title={`${d.month}: ¥${(d.amount || 0).toLocaleString()}`}>
                                            <div
                                                className="w-4 bg-gradient-to-t from-amber-600 to-amber-400 rounded-t-sm hover:from-amber-400 hover:to-amber-300 transition-all cursor-pointer"
                                                style={{ height: `${Math.max(2, height)}%` }}
                                            ></div>
                                        </Tooltip>
                                        <span className="text-[10px] text-slate-500 mt-2 transform -rotate-45">{d.month}</span>
                                    </div>
                                );
                            })}
                        </div>
                    </Card>
                </Col>
            </Row>
        </div>
    );
};

const BookIcon = ({ className }: { className?: string }) => (
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className={className || "w-6 h-6"}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
    </svg>
);

const VoucherIcon = ({ className }: { className?: string }) => (
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className={className || "w-6 h-6"}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
    </svg>
);

const AuditIcon = ({ className }: { className?: string }) => (
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" strokeWidth={1.5} stroke="currentColor" className={className || "w-6 h-6"}>
        <path strokeLinecap="round" strokeLinejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
);

export default Dashboard;
