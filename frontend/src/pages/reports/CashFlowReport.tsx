import React, { useEffect, useState } from 'react';
import { Card, Select, Space, Button, Typography, Row, Col, Statistic, Spin } from 'antd';
import { DownloadOutlined, PrinterOutlined, ArrowUpOutlined, ArrowDownOutlined } from '@ant-design/icons';
import apiClient from '../../api/client';
import { useBook } from '../../context/BookContext';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

interface CashFlowData {
    items: {
        code: string;
        name: string;
        amount: number;
        ytd_amount: number;
    }[];
    net_flow: number;
    ytd_net_flow: number;
}

interface CashFlowReportData {
    operating: CashFlowData;
    investing: CashFlowData;
    financing: CashFlowData;
    net_increase: number;
    ytd_net_increase: number;
}

const CashFlowReport: React.FC = () => {
    const { currentBook } = useBook();
    const [month, setMonth] = useState<number>(dayjs().month() + 1);
    const [data, setData] = useState<CashFlowReportData | null>(null);
    const [loading, setLoading] = useState(false);

    const fetchReport = () => {
        if (!currentBook) return;
        setLoading(true);
        apiClient.get(`/reports/cash-flow-statement?book_code=${currentBook.code}&year=${dayjs().year()}&month=${month}`)
            .then(res => {
                setData(res.data.data);
                setLoading(false);
            })
            .catch(() => setLoading(false));
    };

    useEffect(() => {
        fetchReport();
    }, [currentBook, month]);

    const renderItems = (items: any[]) => {
        return items.filter(i => i.amount !== 0 || i.ytd_amount !== 0).map(item => (
            <div key={item.code} className="grid grid-cols-12 py-2 border-b border-slate-700/50 gap-4">
                <Text className="col-span-8 text-slate-300">{item.name}</Text>
                <Text className="col-span-2 text-right font-mono text-slate-200">{Number(item.amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</Text>
                <Text className="col-span-2 text-right font-mono text-slate-400">{Number(item.ytd_amount).toLocaleString(undefined, { minimumFractionDigits: 2 })}</Text>
            </div>
        ));
    };

    const FlowSection = ({ title, data }: { title: string, data: CashFlowData | undefined }) => {
        if (!data) return null;
        return (
            <Card className="glass-panel mb-6" title={<span className="text-slate-200">{title}</span>}>
                <div className="grid grid-cols-12 mb-2 pb-2 border-b border-slate-700 text-[10px] uppercase tracking-wider text-slate-500 font-bold gap-4">
                    <div className="col-span-8">项目</div>
                    <div className="col-span-2 text-right">本月金额</div>
                    <div className="col-span-2 text-right">本年累计</div>
                </div>
                <div className="space-y-1 mb-4">
                    {renderItems(data.items || [])}
                    {(data.items || []).filter(i => i.amount !== 0 || i.ytd_amount !== 0).length === 0 && <div className="text-slate-500 italic text-center py-4">无相关活动现金流</div>}
                </div>
                <div className="flex justify-between items-center bg-slate-800/50 p-3 rounded">
                    <Text strong className="text-slate-400">活动净现流 (月/年累计)</Text>
                    <div className="flex items-center gap-4">
                        <div className="flex items-center gap-1">
                            {data.net_flow >= 0 ? <ArrowUpOutlined className="text-emerald-500 text-xs" /> : <ArrowDownOutlined className="text-red-500 text-xs" />}
                            <Text strong className={`font-mono ${data.net_flow >= 0 ? 'text-emerald-400' : 'text-red-400'}`}>
                                {(data.net_flow || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                            </Text>
                        </div>
                        <div className="h-4 w-[1px] bg-slate-700"></div>
                        <Text strong className={`font-mono ${data.ytd_net_flow >= 0 ? 'text-emerald-500/80' : 'text-red-500/80'}`}>
                            {(data.ytd_net_flow || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                        </Text>
                    </div>
                </div>
            </Card>
        );
    };



    return (
        <Spin spinning={loading}>
            <div className="space-y-6">
                <div className="flex justify-between items-center">
                    <div>
                        <Title level={2} className="!text-slate-200 !mb-1">现金流量表</Title>
                        <Text className="text-slate-400">分析企业现金流入和流出情况</Text>
                    </div>
                    <Space>
                        <Select
                            value={month}
                            onChange={setMonth}
                            style={{ width: 120 }}
                            options={Array.from({ length: 12 }, (_, i) => ({ label: `${i + 1}月`, value: i + 1 }))}
                        />
                        <Button icon={<PrinterOutlined />} onClick={() => window.print()}>打印</Button>
                        <Button type="primary" icon={<DownloadOutlined />}>导出</Button>
                    </Space>
                </div>

                <Row gutter={16}>
                    <Col span={8}>
                        <Card className="glass-panel text-center">
                            <Statistic
                                title={<span className="text-slate-400">经营净流 (月/年)</span>}
                                value={data?.operating.net_flow || 0}
                                precision={2}
                                valueStyle={{ color: (data?.operating.net_flow || 0) >= 0 ? '#10b981' : '#f87171', fontSize: '18px' }}
                                suffix={<span className="text-[10px] text-slate-500 ml-2">/ {(data?.operating.ytd_net_flow || 0).toLocaleString()}</span>}
                            />
                        </Card>
                    </Col>
                    <Col span={8}>
                        <Card className="glass-panel text-center">
                            <Statistic
                                title={<span className="text-slate-400">投资净流 (月/年)</span>}
                                value={data?.investing.net_flow || 0}
                                precision={2}
                                valueStyle={{ color: (data?.investing.net_flow || 0) >= 0 ? '#10b981' : '#f87171', fontSize: '18px' }}
                                suffix={<span className="text-[10px] text-slate-500 ml-2">/ {(data?.investing.ytd_net_flow || 0).toLocaleString()}</span>}
                            />
                        </Card>
                    </Col>
                    <Col span={8}>
                        <Card className="glass-panel text-center">
                            <Statistic
                                title={<span className="text-slate-400">筹资净流 (月/年)</span>}
                                value={data?.financing.net_flow || 0}
                                precision={2}
                                valueStyle={{ color: (data?.financing.net_flow || 0) >= 0 ? '#10b981' : '#f87171', fontSize: '18px' }}
                                suffix={<span className="text-[10px] text-slate-500 ml-2">/ {(data?.financing.ytd_net_flow || 0).toLocaleString()}</span>}
                            />
                        </Card>
                    </Col>
                </Row>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <FlowSection title="1. 经营活动产生的现金流量" data={data?.operating} />
                        <FlowSection title="2. 投资活动产生的现金流量" data={data?.investing} />
                    </div>
                    <div>
                        <FlowSection title="3. 筹资活动产生的现金流量" data={data?.financing} />

                        <Card className="bg-gradient-to-br from-slate-800 to-slate-900 border-slate-700">
                            <div className="text-center py-6">
                                <Text className="text-slate-400 block mb-2 text-lg">现金及现金等价物净增加额 (本年累计)</Text>
                                <Title level={1} className={`!m-0 font-mono ${(data?.ytd_net_increase || 0) >= 0 ? '!text-emerald-400' : '!text-red-400'}`}>
                                    ¥ {(data?.ytd_net_increase || 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                </Title>
                                <Text className="text-slate-500 mt-2 block">本月增加: ¥ {(data?.net_increase || 0).toLocaleString()}</Text>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </Spin>
    );
};

export default CashFlowReport;
