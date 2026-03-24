import React, { useEffect, useState } from 'react';
import { Table, Card, Select, Form, Button, DatePicker, message, Tag } from 'antd';
import { generalLedgerApi } from '../api/generalLedger';
import dayjs from 'dayjs';

const ChronologicalLedgerPage: React.FC = () => {
  const [vouchers, setVouchers] = useState<any[]>([]);
  const [loading, setLoading] = useState(false);
  const [form] = Form.useForm();

  const fetchChronological = async (values: any) => {
    setLoading(true);
    try {
      const params: any = { period_id: values.period_id };
      if (values.date_range) {
        params.start_date = values.date_range[0].format('YYYY-MM-DD');
        params.end_date = values.date_range[1].format('YYYY-MM-DD');
      }
      const response = await generalLedgerApi.chronological(params);
      setVouchers(response.data);
    } catch (error) {
      message.error('获取序时账失败');
    } finally {
      setLoading(false);
    }
  };

  const expandedRowRender = (record: any) => {
    const columns = [
      { title: '科目', dataIndex: 'account', key: 'account' },
      { title: '摘要', dataIndex: 'summary', key: 'summary' },
      {
        title: '借方',
        dataIndex: 'debit',
        key: 'debit',
        align: 'right' as const,
        render: (amount: string) => amount !== '0.00' ? `¥${parseFloat(amount).toFixed(2)}` : '',
      },
      {
        title: '贷方',
        dataIndex: 'credit',
        key: 'credit',
        align: 'right' as const,
        render: (amount: string) => amount !== '0.00' ? `¥${parseFloat(amount).toFixed(2)}` : '',
      },
    ];

    return <Table columns={columns} dataSource={record.lines} pagination={false} rowKey={(r, i) => `${record.voucher_no}-${i}`} />;
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
      title: '凭证类型',
      dataIndex: 'voucher_type',
      key: 'voucher_type',
      render: (type: string) => {
        const typeMap: Record<string, { color: string; text: string }> = {
          receipt: { color: 'green', text: '收款' },
          payment: { color: 'red', text: '付款' },
          transfer: { color: 'blue', text: '转账' },
        };
        const config = typeMap[type] || { color: 'default', text: type };
        return <Tag color={config.color}>{config.text}</Tag>;
      },
    },
    {
      title: '摘要',
      dataIndex: 'summary',
      key: 'summary',
    },
    {
      title: '借方合计',
      dataIndex: 'total_debit',
      key: 'total_debit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '贷方合计',
      dataIndex: 'total_credit',
      key: 'total_credit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
  ];

  return (
    <Card title="序时账">
      <Form form={form} onFinish={fetchChronological} layout="inline" style={{ marginBottom: 16 }}>
        <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
          <Select style={{ width: 200 }} placeholder="选择会计期间">
            <Select.Option value={1}>2024-01</Select.Option>
            <Select.Option value={2}>2024-02</Select.Option>
          </Select>
        </Form.Item>
        <Form.Item name="date_range" label="日期范围">
          <DatePicker.RangePicker />
        </Form.Item>
        <Form.Item>
          <Button type="primary" htmlType="submit">
            查询
          </Button>
        </Form.Item>
      </Form>

      <Table
        columns={columns}
        dataSource={vouchers}
        loading={loading}
        rowKey="voucher_no"
        expandable={{ expandedRowRender }}
        pagination={{ pageSize: 20 }}
      />
    </Card>
  );
};

export default ChronologicalLedgerPage;
