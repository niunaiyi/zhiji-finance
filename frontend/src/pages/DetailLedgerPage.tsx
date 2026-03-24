import React, { useEffect, useState } from 'react';
import { Table, Card, Select, Form, Button, message } from 'antd';
import { generalLedgerApi, DetailLedgerEntry } from '../api/generalLedger';

const DetailLedgerPage: React.FC = () => {
  const [entries, setEntries] = useState<DetailLedgerEntry[]>([]);
  const [loading, setLoading] = useState(false);
  const [form] = Form.useForm();

  const fetchDetailLedger = async (values: any) => {
    setLoading(true);
    try {
      const response = await generalLedgerApi.detailLedger({
        period_id: values.period_id,
        account_id: values.account_id,
      });
      setEntries(response.data);
    } catch (error) {
      message.error('获取明细账失败');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    {
      title: '凭证号',
      dataIndex: 'voucher_no',
      key: 'voucher_no',
    },
    {
      title: '凭证日期',
      dataIndex: 'voucher_date',
      key: 'voucher_date',
    },
    {
      title: '摘要',
      dataIndex: 'summary',
      key: 'summary',
    },
    {
      title: '借方金额',
      dataIndex: 'debit',
      key: 'debit',
      align: 'right' as const,
      render: (amount: string) => amount !== '0.00' ? `¥${parseFloat(amount).toFixed(2)}` : '',
    },
    {
      title: '贷方金额',
      dataIndex: 'credit',
      key: 'credit',
      align: 'right' as const,
      render: (amount: string) => amount !== '0.00' ? `¥${parseFloat(amount).toFixed(2)}` : '',
    },
  ];

  return (
    <Card title="明细账">
      <Form form={form} onFinish={fetchDetailLedger} layout="inline" style={{ marginBottom: 16 }}>
        <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
          <Select style={{ width: 200 }} placeholder="选择会计期间">
            {/* TODO: Load periods from API */}
            <Select.Option value={1}>2024-01</Select.Option>
            <Select.Option value={2}>2024-02</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item name="account_id" label="会计科目" rules={[{ required: true }]}>
          <Select style={{ width: 200 }} placeholder="选择会计科目">
            {/* TODO: Load accounts from API */}
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
        dataSource={entries}
        loading={loading}
        rowKey={(record, index) => `${record.voucher_no}-${index}`}
        pagination={{ pageSize: 20 }}
      />
    </Card>
  );
};

export default DetailLedgerPage;
