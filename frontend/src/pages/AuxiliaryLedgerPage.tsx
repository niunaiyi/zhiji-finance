import React, { useState } from 'react';
import { Table, Card, Select, Form, Button, message } from 'antd';
import { generalLedgerApi } from '../api/generalLedger';

const AuxiliaryLedgerPage: React.FC = () => {
  const [balances, setBalances] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [form] = Form.useForm();

  const fetchAuxiliaryLedger = async (values: any) => {
    setLoading(true);
    try {
      const response = await generalLedgerApi.auxiliaryLedger({
        period_id: values.period_id,
        aux_category_id: values.aux_category_id,
        aux_item_id: values.aux_item_id,
      });
      setBalances(response.data);
    } catch (error) {
      message.error('获取辅助核算账失败');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    {
      title: '科目',
      dataIndex: ['account', 'name'],
      key: 'account',
    },
    {
      title: '辅助核算类别',
      dataIndex: ['aux_category', 'name'],
      key: 'category',
    },
    {
      title: '辅助核算项目',
      dataIndex: ['aux_item', 'name'],
      key: 'item',
    },
    {
      title: '期初借方',
      dataIndex: 'opening_debit',
      key: 'opening_debit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '期初贷方',
      dataIndex: 'opening_credit',
      key: 'opening_credit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '本期借方',
      dataIndex: 'period_debit',
      key: 'period_debit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '本期贷方',
      dataIndex: 'period_credit',
      key: 'period_credit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '期末借方',
      dataIndex: 'closing_debit',
      key: 'closing_debit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '期末贷方',
      dataIndex: 'closing_credit',
      key: 'closing_credit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
  ];

  return (
    <Card title="辅助核算账">
      <Form form={form} onFinish={fetchAuxiliaryLedger} layout="inline" style={{ marginBottom: 16 }}>
        <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
          <Select style={{ width: 200 }} placeholder="选择会计期间">
            <Select.Option value={1}>2024-01</Select.Option>
            <Select.Option value={2}>2024-02</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item name="aux_category_id" label="辅助核算类别" rules={[{ required: true }]}>
          <Select style={{ width: 200 }} placeholder="选择类别">
            {/* TODO: Load aux categories from API */}
            <Select.Option value={1}>客户</Select.Option>
            <Select.Option value={2}>供应商</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item name="aux_item_id" label="辅助核算项目">
          <Select style={{ width: 200 }} placeholder="选择项目（可选）">
            {/* TODO: Load aux items from API */}
          </Select>
        </Form.Item>
        <Form.Item>
          <Button type="primary" htmlType="submit">
            查询
          </Button>
        </Form.Item>
      </Form>

      <Table
        columns={columns}
        dataSource={balances}
        loading={loading}
        rowKey={(record, index) => `${record.account?.code}-${record.aux_item?.code}-${index}`}
        pagination={{ pageSize: 20 }}
      />
    </Card>
  );
};

export default AuxiliaryLedgerPage;
