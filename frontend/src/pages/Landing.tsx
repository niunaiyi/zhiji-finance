import React, { useState } from 'react';
import { Button, Row, Col, Card, Slider, Table, Tag, Modal, message } from 'antd';
import { RocketOutlined, WalletOutlined, ThunderboltOutlined, RiseOutlined } from '@ant-design/icons';

const Landing: React.FC = () => {
    const [apy, setApy] = useState(120);
    const [stakeAmount, setStakeAmount] = useState(1000);
    const [isWalletModalOpen, setIsWalletModalOpen] = useState(false);

    const pools = [
        { key: '1', pair: 'ETH-USDC', apy: '120%', tvl: '$450M', risk: 'Low' },
        { key: '2', pair: 'BTC-ETH', apy: '85%', tvl: '$1.2B', risk: 'Low' },
        { key: '3', pair: 'SOL-USDC', apy: '240%', tvl: '$120M', risk: 'Medium' },
        { key: '4', pair: 'NEON-ETH', apy: '1200%', tvl: '$5M', risk: 'High' },
    ];

    const columns = [
        { title: 'Pool', dataIndex: 'pair', key: 'pair', render: (text: string) => <span className="text-lg font-bold text-slate-200">{text}</span> },
        { title: 'APY', dataIndex: 'apy', key: 'apy', render: (text: string) => <span className="text-neon-mint font-bold text-shadow-neon">{text}</span> },
        { title: 'TVL', dataIndex: 'tvl', key: 'tvl', render: (text: string) => <span className="text-slate-400 font-mono">{text}</span> },
        {
            title: 'Risk',
            dataIndex: 'risk',
            key: 'risk',
            render: (risk: string) => (
                <Tag color={risk === 'Low' ? '#00FF94' : risk === 'Medium' ? '#00D4FF' : '#FF0055'} className="border-0 bg-opacity-20">
                    {risk.toUpperCase()}
                </Tag>
            )
        },
        {
            title: 'Action',
            key: 'action',
            render: () => <Button type="primary" size="small" className="bg-neon-magenta border-0 hover:bg-magenta-600">Stake</Button>
        }
    ];

    const handleConnect = () => {
        setIsWalletModalOpen(true);
    };

    const handleWalletConnect = () => {
        message.success("Wallet Connected Successfully!");
        setIsWalletModalOpen(false);
    };

    return (
        <div className="min-h-screen relative overflow-hidden text-slate-200 font-sans">
            {/* Background Effects */}
            <div className="absolute top-0 left-0 w-full h-96 bg-gradient-to-b from-blue-900/20 to-transparent pointer-events-none" />

            {/* Navbar */}
            <div className="relative z-10 flex justify-between items-center px-8 py-6 border-b border-white/5 backdrop-blur-md sticky top-0 bg-slate-900/80">
                <div className="text-2xl font-display font-bold text-transparent bg-clip-text bg-gradient-to-r from-neon-mint to-cyan-400 tracking-wider">
                    NEON<span className="text-white">DEFI</span>
                </div>
                <div className="flex gap-4">
                    <Button type="text" className="text-slate-300 hover:text-white">Markets</Button>
                    <Button type="text" className="text-slate-300 hover:text-white">Exchange</Button>
                    <Button type="text" className="text-slate-300 hover:text-white">Governance</Button>
                    <Button
                        icon={<WalletOutlined />}
                        className="bg-transparent border border-neon-mint text-neon-mint hover:text-black hover:bg-neon-mint hover:border-neon-mint transition-all duration-300 shadow-[0_0_10px_rgba(0,255,148,0.2)] hover:shadow-[0_0_20px_rgba(0,255,148,0.6)]"
                        onClick={handleConnect}
                    >
                        Connect Wallet
                    </Button>
                </div>
            </div>

            {/* Hero Section */}
            <div className="container mx-auto px-4 pt-20 pb-16 relative z-10">
                <Row gutter={[48, 48]} align="middle">
                    <Col xs={24} lg={12}>
                        <h1 className="text-6xl md:text-7xl font-display font-bold leading-tight mb-6">
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400">Future of </span>
                            <br />
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-neon-mint to-cyan-400 text-shadow-neon">Yield Farming</span>
                        </h1>
                        <p className="text-xl text-slate-400 mb-8 max-w-lg leading-relaxed">
                            Maximize your crypto assets with AI-driven yield strategies.
                            Automated liquidity management on the most advanced cyberpunk protocol.
                        </p>
                        <div className="flex gap-6">
                            <Button type="primary" size="large" className="h-12 px-8 text-lg bg-neon-mint text-black border-0 font-bold hover:scale-105 transition-transform" icon={<RocketOutlined />}>
                                Launch App
                            </Button>
                            <Button size="large" className="h-12 px-8 text-lg bg-transparent border border-slate-700 text-white hover:border-white hover:text-white" icon={<ThunderboltOutlined />}>
                                Read Whitepaper
                            </Button>
                        </div>

                        <div className="mt-12 flex gap-8">
                            <div>
                                <div className="text-3xl font-display font-bold text-white">$1.2B+</div>
                                <div className="text-sm text-slate-500 uppercase tracking-widest">TVL Locked</div>
                            </div>
                            <div>
                                <div className="text-3xl font-display font-bold text-white">450K+</div>
                                <div className="text-sm text-slate-500 uppercase tracking-widest">Users</div>
                            </div>
                            <div>
                                <div className="text-3xl font-display font-bold text-white">24/7</div>
                                <div className="text-sm text-slate-500 uppercase tracking-widest">Uptime</div>
                            </div>
                        </div>
                    </Col>

                    {/* APY Calculator */}
                    <Col xs={24} lg={12}>
                        <div className="glass-panel p-8 rounded-3xl relative overflow-hidden group">
                            <div className="absolute top-0 right-0 w-64 h-64 bg-neon-magenta/20 rounded-full blur-3xl -mr-32 -mt-32 pointer-events-none"></div>
                            <div className="absolute bottom-0 left-0 w-64 h-64 bg-neon-mint/10 rounded-full blur-3xl -ml-32 -mb-32 pointer-events-none"></div>

                            <h3 className="text-2xl font-display font-bold mb-6 flex items-center gap-3">
                                <RiseOutlined className="text-neon-magenta" />
                                ROI Calculator
                            </h3>

                            <div className="mb-8">
                                <div className="flex justify-between mb-2">
                                    <span className="text-slate-400">Deposit Amount</span>
                                    <span className="text-xl font-bold font-mono">${stakeAmount.toLocaleString()}</span>
                                </div>
                                <Slider
                                    min={100}
                                    max={100000}
                                    value={stakeAmount}
                                    onChange={setStakeAmount}
                                    trackStyle={{ background: 'linear-gradient(90deg, #00FF94, #00D4FF)' }}
                                    handleStyle={{ borderColor: '#00FF94', background: '#050511', width: 20, height: 20, marginTop: -8 }}
                                    railStyle={{ background: 'rgba(255,255,255,0.1)' }}
                                />
                            </div>

                            <div className="mb-8">
                                <div className="flex justify-between mb-2">
                                    <span className="text-slate-400">Estimated APY</span>
                                    <span className="text-xl font-bold font-mono text-neon-mint">{apy}%</span>
                                </div>
                                <Slider
                                    min={10}
                                    max={5000}
                                    value={apy}
                                    onChange={setApy}
                                    trackStyle={{ background: 'linear-gradient(90deg, #FF00FF, #8B5CF6)' }}
                                    handleStyle={{ borderColor: '#FF00FF', background: '#050511', width: 20, height: 20, marginTop: -8 }}
                                    railStyle={{ background: 'rgba(255,255,255,0.1)' }}
                                />
                            </div>

                            <div className="bg-slate-900/50 p-6 rounded-2xl border border-white/5">
                                <div className="text-slate-400 mb-1">Daily Earnings</div>
                                <div className="text-3xl font-mono font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-300">
                                    ${((stakeAmount * (apy / 100)) / 365).toFixed(2)}
                                </div>
                                <div className="text-slate-500 text-sm mt-1">
                                    Yearly: <span className="text-neon-mint">${(stakeAmount * (apy / 100)).toLocaleString()}</span>
                                </div>
                            </div>
                        </div>
                    </Col>
                </Row>
            </div>

            {/* Feature Section: Liquidity Pools */}
            <div className="container mx-auto px-4 py-16">
                <div className="flex justify-between items-end mb-8">
                    <div>
                        <h2 className="text-4xl font-display font-bold mb-2">Top Liquidity Pools</h2>
                        <p className="text-slate-400">Provide liquidity and earn yield on top pairs</p>
                    </div>
                    <Button type="default" className="border-slate-700 text-slate-300 hover:text-white hover:border-white">View All Pools</Button>
                </div>

                <Card className="glass-panel border-0 neon-border rounded-2xl overflow-hidden" styles={{ body: { padding: 0 } }}>
                    <Table
                        columns={columns}
                        dataSource={pools}
                        pagination={false}
                        rowClassName="hover:bg-white/5 transition-colors cursor-pointer"
                    />
                </Card>
            </div>

            {/* Wallet Modal */}
            <Modal
                title={<span className="text-xl font-display font-bold">Connect Wallet</span>}
                open={isWalletModalOpen}
                onCancel={() => setIsWalletModalOpen(false)}
                footer={null}
                width={400}
                className="neon-modal"
                centered
            >
                <div className="flex flex-col gap-3 py-4">
                    <Button size="large" className="h-14 flex items-center justify-between px-6 bg-slate-800 border-slate-700 hover:border-neon-mint group" onClick={handleWalletConnect}>
                        <span className="font-bold text-slate-200 group-hover:text-neon-mint transition-colors">MetaMask</span>
                        <div className="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center">🦊</div>
                    </Button>
                    <Button size="large" className="h-14 flex items-center justify-between px-6 bg-slate-800 border-slate-700 hover:border-cyan-400 group" onClick={handleWalletConnect}>
                        <span className="font-bold text-slate-200 group-hover:text-cyan-400 transition-colors">WalletConnect</span>
                        <div className="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">📡</div>
                    </Button>
                    <Button size="large" className="h-14 flex items-center justify-between px-6 bg-slate-800 border-slate-700 hover:border-purple-400 group" onClick={handleWalletConnect}>
                        <span className="font-bold text-slate-200 group-hover:text-purple-400 transition-colors">Phantom</span>
                        <div className="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">👻</div>
                    </Button>
                </div>
            </Modal>
        </div>
    );
};

export default Landing;
