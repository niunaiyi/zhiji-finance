import React, { useEffect, useState } from 'react';
import { Card, Table, Button, Space, Typography, Modal, Form, Input, Select, message, Tag, Statistic, Row, Col } from 'antd';
import { PlusOutlined, CalculatorOutlined, FileSearchOutlined, SettingOutlined } from '@ant-design/icons';
import { payrollApi, type PayrollRecord } from '../api/payroll';
import dayjs from 'dayjs';

const { Title, Text } = Typography;

const PayrollManagement: React.FC = () => {
    const [payrolls, setPayrolls] = useState<PayrollRecord[]>([]);
    const [items, setItems] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [calcModalOpen, setCalcModalOpen] = useState(false);
    const [itemModalOpen, setItemModalOpen] = useState(false);
    const [calcForm] = Form.useForm();
    const [itemForm] = Form.useForm();

    const fetchPayrolls = () => {
        setLoading(true);
        payrollApi.listPayrolls().then(res => {
            setPayrolls(res.data.data || res.data);
            setLoading(false);
        }).catch(() => setLoading(false));
    };

    const fetchItems = () => {
        payrollApi.listItems().then(res => {
            setItems(res.data.data || res.data);
        });
    };

    useEffect(() => {
        fetchPayrolls();
        fetchItems();
    }, []);

    const handleCalculate = () => {
        calcForm.validateFields().then(values => {
            setLoading(true);
            payrollApi.calculate({
                period_id: values.period_id // Backend expects period_id
            }).then(() => {
                message.success('工资表生成成功');
                setCalcModalOpen(false);
                fetchPayrolls();
            }).catch(err => {
                message.error(err.response?.data?.message || '生成失败');
            }).finally(() => setLoading(false));
        });
    };

    const handleSaveItem = () => {
        itemForm.validateFields().then(values => {
            payrollApi.saveItem(values).then(() => {
                message.success('保存成功');
                setItemModalOpen(false);
                fetchItems();
            }).catch(() => message.error('保存失败'));
        });
    };

    const payrollColumns = [
        { title: '编号', dataIndex: 'payroll_no', key: 'payroll_no' },
        { title: '期间', key: 'period', render: (_: any, record: any) => `${record.period?.year}年${record.period?.month}月` },
        { title: '日期', dataIndex: 'payroll_date', key: 'payroll_date', render: (val: string) => dayjs(val).format('YYYY-MM-DD') },
        { 
            title: '状态', 
            dataIndex: 'status', 
            key: 'status',
            render: (status: string) => (
                <Tag color={status === 'posted' ? 'blue' : 'orange'}>
                    {status === 'posted' ? '已入账' : '早稿'}
                </Tag>
            )
        },
        { 
            title: '总实发', 
            key: 'total',
            render: (_: any, record: any) => (
                <Text strong>¥{record.lines?.reduce((sum: number, line: any) => sum + Number(line.net_pay), 0).toLocaleString()}</Text>
            )
        },
        {
            title: '操作',
            key: 'action',
            render: (_: any, record: any) => (
                <Button size="small" icon={<FileSearchOutlined />} onClick={() => {
                    Modal.info({
                        title: `工资表明细 - ${record.payroll_no}`,
                        width: 800,
                        content: (
                            <Table 
                                size="small"
                                dataSource={record.lines}
                                pagination={false}
                                columns={[
                                    { title: '职员', dataIndex: ['employee', 'name'], key: 'name' },
                                    { title: '应发工资', dataIndex: 'total_earning', align: 'right' },
                                    { title: '扣款', dataIndex: 'total_deduction', align: 'right' },
                                    { title: '实发金额', dataIndex: 'net_pay', align: 'right', render: (v) => <Text strong className="text-emerald-500">{v}</Text> },
                                ]}
                            />
                        )
                    });
                }}>明细</Button>
            )
        }
    ];

    const itemColumns = [
        { title: '编码', dataIndex: 'code', key: 'code' },
        { title: '名称', dataIndex: 'name', key: 'name' },
        { title: '类型', dataIndex: 'type', render: (t: string) => t === 'earning' ? '收入' : '扣减' },
        { title: '启用', dataIndex: 'is_active', render: (v: boolean) => v ? '是' : '否' },
    ];

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <Title level={2} className="!text-slate-200 !mb-1">薪酬管理</Title>
                    <p className="text-slate-400">职员工资发放与核算管理</p>
                </div>
                <Space>
                    <Button icon={<SettingOutlined />} onClick={() => setItemModalOpen(true)}>工资项目</Button>
                    <Button type="primary" icon={<CalculatorOutlined />} onClick={() => setCalcModalOpen(true)}>计算本月工资</Button>
                </Space>
            </div>

            <Row gutter={16}>
                <Col span={8}>
                    <Card className="glass-panel">
                        <Statistic title="本年累计支出" value={payrolls.reduce((sum, p) => sum + p.lines.reduce((s:any, l:any) => s + Number(l.net_pay), 0), 0)} precision={2} prefix="¥" valueStyle={{ color: '#F59E0B' }} />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card className="glass-panel">
                        <Statistic title="上月发放总额" value={payrolls.length > 0 ? payrolls[payrolls.length-1].lines.reduce((s:any, l:any) => s + Number(l.net_pay), 0) : 0} precision={2} prefix="¥" />
                    </Card>
                </Col>
                <Col span={8}>
                    <Card className="glass-panel">
                        <Statistic title="覆盖职员数" value={payrolls.length > 0 ? payrolls[payrolls.length-1].lines.length : 0} />
                    </Card>
                </Col>
            </Row>

            <Card className="glass-panel">
                <Table dataSource={payrolls} columns={payrollColumns} loading={loading} rowKey="id" />
            </Card>

            <Modal title="工资项目管理" open={itemModalOpen} onCancel={() => setItemModalOpen(false)} footer={null} width={800}>
                <div className="mb-4 flex justify-end">
                    <Button type="primary" size="small" icon={<PlusOutlined />} onClick={() => { itemForm.resetFields(); }}>新增项目</Button>
                </div>
                <Table size="small" dataSource={items} columns={itemColumns} pagination={false} rowKey="id" />
                <Form form={itemForm} layout="inline" className="mt-4 border-t pt-4" onFinish={handleSaveItem}>
                    <Form.Item name="code" label="编码" rules={[{required: true}]}><Input style={{width: 100}} /></Form.Item>
                    <Form.Item name="name" label="名称" rules={[{required: true}]}><Input style={{width: 150}} /></Form.Item>
                    <Form.Item name="type" label="类型" initialValue="earning"><Select options={[{label:'收入', value:'earning'}, {label:'扣减', value:'deduction'}]} /></Form.Item>
                    <Form.Item><Button type="primary" htmlType="submit">保存</Button></Form.Item>
                </Form>
            </Modal>

            <Modal title="工资核算" open={calcModalOpen} onOk={handleCalculate} onCancel={() => setCalcModalOpen(false)} loading={loading}>
                <Form form={calcForm} layout="vertical">
                    <Form.Item name="period_id" label="选择期间" rules={[{ required: true }]} initialValue={1}>
                        <Select options={[
                            { label: '2024年1月', value: 1 },
                            { label: '2024年2月', value: 2 },
                            { label: '2024年3月', value: 3 },
                        ]} />
                    </Form.Item>
                    <p className="text-slate-500 text-xs mt-2">系统将根据职员档案中的基础工资自动生成工资发放预览。</p>
                </Form>
            </Modal>
        </div>
    );
};

export default PayrollManagement;
