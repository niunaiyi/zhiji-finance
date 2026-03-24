import React, { useEffect, useState } from 'react';
import { Table, Button, Space, Tag, Modal, Form, Input, DatePicker, Select, InputNumber, message, Card } from 'antd';
import { PlusOutlined, EyeOutlined, CheckOutlined, FileTextOutlined, CloseOutlined } from '@ant-design/icons';
import { vouchersApi, Voucher, CreateVoucherRequest } from '../api/vouchers';
import dayjs from 'dayjs';

const VoucherManagement: React.FC = () => {
  const [vouchers, setVouchers] = useState<Voucher[]>([]);
  const [loading, setLoading] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [form] = Form.useForm();
  const [accounts, setAccounts] = useState<any[]>([]);

  useEffect(() => {
    fetchVouchers();
    // TODO: Fetch accounts from API
  }, []);

  const fetchVouchers = async () => {
    setLoading(true);
    try {
      const response = await vouchersApi.list();
      setVouchers(response.data.data);
    } catch (error) {
      message.error('获取凭证列表失败');
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async (values: any) => {
    try {
      const data: CreateVoucherRequest = {
        period_id: values.period_id,
        voucher_type: values.voucher_type,
        voucher_date: values.voucher_date.format('YYYY-MM-DD'),
        summary: values.summary,
        lines: values.lines.map((line: any) => ({
          account_id: line.account_id,
          summary: line.summary,
          debit: line.debit || '0.00',
          credit: line.credit || '0.00',
        })),
      };
      await vouchersApi.create(data);
      message.success('凭证创建成功');
      setIsModalOpen(false);
      form.resetFields();
      fetchVouchers();
    } catch (error) {
      message.error('凭证创建失败');
    }
  };

  const handleReview = async (id: number) => {
    try {
      await vouchersApi.review(id);
      message.success('审核成功');
      fetchVouchers();
    } catch (error) {
      message.error('审核失败');
    }
  };

  const handlePost = async (id: number) => {
    try {
      await vouchersApi.post(id);
      message.success('过账成功');
      fetchVouchers();
    } catch (error) {
      message.error('过账失败');
    }
  };

  const handleReverse = async (id: number) => {
    try {
      await vouchersApi.reverse(id);
      message.success('红冲成功');
      fetchVouchers();
    } catch (error) {
      message.error('红冲失败');
    }
  };

  const handleVoid = async (id: number) => {
    try {
      await vouchersApi.void(id);
      message.success('作废成功');
      fetchVouchers();
    } catch (error) {
      message.error('作废失败');
    }
  };

  const getStatusTag = (status: string) => {
    const statusMap: Record<string, { color: string; text: string }> = {
      draft: { color: 'default', text: '草稿' },
      reviewed: { color: 'blue', text: '已审核' },
      posted: { color: 'green', text: '已记账' },
      reversed: { color: 'orange', text: '已红冲' },
      voided: { color: 'red', text: '已作废' },
    };
    const config = statusMap[status] || { color: 'default', text: status };
    return <Tag color={config.color}>{config.text}</Tag>;
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
        const typeMap: Record<string, string> = {
          receipt: '收款',
          payment: '付款',
          transfer: '转账',
        };
        return typeMap[type] || type;
      },
    },
    {
      title: '摘要',
      dataIndex: 'summary',
      key: 'summary',
    },
    {
      title: '借方金额',
      dataIndex: 'total_debit',
      key: 'total_debit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '贷方金额',
      dataIndex: 'total_credit',
      key: 'total_credit',
      align: 'right' as const,
      render: (amount: string) => `¥${parseFloat(amount).toFixed(2)}`,
    },
    {
      title: '状态',
      dataIndex: 'status',
      key: 'status',
      render: (status: string) => getStatusTag(status),
    },
    {
      title: '操作',
      key: 'action',
      render: (_: any, record: Voucher) => (
        <Space size="small">
          <Button size="small" icon={<EyeOutlined />}>查看</Button>
          {record.status === 'draft' && (
            <Button size="small" type="primary" icon={<CheckOutlined />} onClick={() => handleReview(record.id)}>
              审核
            </Button>
          )}
          {record.status === 'reviewed' && (
            <Button size="small" type="primary" icon={<FileTextOutlined />} onClick={() => handlePost(record.id)}>
              过账
            </Button>
          )}
          {record.status === 'posted' && (
            <Button size="small" danger onClick={() => handleReverse(record.id)}>
              红冲
            </Button>
          )}
          {(record.status === 'draft' || record.status === 'reviewed') && (
            <Button size="small" danger icon={<CloseOutlined />} onClick={() => handleVoid(record.id)}>
              作废
            </Button>
          )}
        </Space>
      ),
    },
  ];

  return (
    <Card title="凭证管理" extra={
      <Button type="primary" icon={<PlusOutlined />} onClick={() => setIsModalOpen(true)}>
        新建凭证
      </Button>
    }>
      <Table
        columns={columns}
        dataSource={vouchers}
        loading={loading}
        rowKey="id"
        pagination={{ pageSize: 10 }}
      />

      <Modal
        title="新建凭证"
        open={isModalOpen}
        onCancel={() => setIsModalOpen(false)}
        onOk={() => form.submit()}
        width={800}
      >
        <Form form={form} onFinish={handleCreate} layout="vertical">
          <Form.Item name="period_id" label="会计期间" rules={[{ required: true }]}>
            <Select placeholder="选择会计期间">
              {/* TODO: Load periods from API */}
              <Select.Option value={1}>2024-01</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item name="voucher_type" label="凭证类型" rules={[{ required: true }]}>
            <Select>
              <Select.Option value="receipt">收款凭证</Select.Option>
              <Select.Option value="payment">付款凭证</Select.Option>
              <Select.Option value="transfer">转账凭证</Select.Option>
            </Select>
          </Form.Item>
          <Form.Item name="voucher_date" label="凭证日期" rules={[{ required: true }]}>
            <DatePicker style={{ width: '100%' }} />
          </Form.Item>
          <Form.Item name="summary" label="摘要">
            <Input />
          </Form.Item>
          <Form.List name="lines">
            {(fields, { add, remove }) => (
              <>
                {fields.map((field) => (
                  <Space key={field.key} style={{ display: 'flex', marginBottom: 8 }} align="baseline">
                    <Form.Item {...field} name={[field.name, 'account_id']} rules={[{ required: true }]}>
                      <Select placeholder="科目" style={{ width: 200 }}>
                        {/* TODO: Load accounts from API */}
                      </Select>
                    </Form.Item>
                    <Form.Item {...field} name={[field.name, 'summary']}>
                      <Input placeholder="摘要" style={{ width: 150 }} />
                    </Form.Item>
                    <Form.Item {...field} name={[field.name, 'debit']}>
                      <InputNumber placeholder="借方" min={0} precision={2} style={{ width: 120 }} />
                    </Form.Item>
                    <Form.Item {...field} name={[field.name, 'credit']}>
                      <InputNumber placeholder="贷方" min={0} precision={2} style={{ width: 120 }} />
                    </Form.Item>
                    <Button onClick={() => remove(field.name)}>删除</Button>
                  </Space>
                ))}
                <Form.Item>
                  <Button type="dashed" onClick={() => add()} block icon={<PlusOutlined />}>
                    添加凭证行
                  </Button>
                </Form.Item>
              </>
            )}
          </Form.List>
        </Form>
      </Modal>
    </Card>
  );
};

export default VoucherManagement;
