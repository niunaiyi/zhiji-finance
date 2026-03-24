import React, { useEffect, useState } from 'react';
import { Table, Card, Select, Button, message } from 'antd';
import { generalLedgerApi, Balance } from '../api/generalLedger';

const BalanceSheetPage: React.FC = () => {
  const [balances, setBalances] = useState<Balance[]>([]);
  const [loading, setLoading] = useState(false);
  const [periodId, setPeriodId] = useState<number>(1);

  useEffect(() => {
    if (periodId) {
      fetchBalanceSheet();
    }
  }, [periodId]);

  const fetchBalanceSheet = async () => {
    setLoading(true);
    try {
      const response = await generalLedgerApi.balanceSheet({ period_id: periodId });
      setBalances(response.data);
    } catch (error) {
      message.error('获取科目余额表失败');
    } finally {
      setLoading(false);
    }
  };

  const columns = [
    {
      title: '科目编码',
      dataIndex: ['account', 'code'],
      key: 'code',
    },
    {
      title: '科目名称',
      dataIndex: ['account', 'name'],
      key: 'name',
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
    <Card
      title="科目余额表"
      extra={
        <Select
          value={periodId}
          onChange={setPeriodId}
          style={{ width: 200 }}
          placeholder="选择会计期间"
        >
          {/* TODO: Load periods from API */}
          <Select.Option value={1}>2024-01</Select.Option>
          <Select.Option value={2}>2024-02</Select.Option>
        </Select>
      }
    >
      <Table
        columns={columns}
        dataSource={balances}
        loading={loading}
        rowKey={(record) => record.account.code}
        pagination={false}
      />
    </Card>
  );
};

export default BalanceSheetPage;
