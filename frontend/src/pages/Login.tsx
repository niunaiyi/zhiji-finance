import React, { useState } from 'react';
import { Form, Input, Button, Card, message } from 'antd';
import { UserOutlined, LockOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';

export const Login: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { login, selectCompany } = useAuth();

  const onFinish = async (values: { email: string; password: string }) => {
    setLoading(true);
    try {
      const response = await authApi.login(values.email, values.password);
      
      // 1. SuperAdmins ALWAYS go to admin panel
      if (response.user.is_super_admin) {
        // We still log them in with whatever company context they have (if any)
        login(response.user, response.companies, response.token);
        navigate('/admin');
        return;
      }

      // 2. Normal users MUST have at least one company
      const firstCompany = response.companies[0];
      if (firstCompany) {
        // Auto-select the first (and only) company for normal users
        const selectRes = await authApi.selectCompany(firstCompany.id, response.token);
        login(response.user, response.companies, response.token);
        selectCompany(selectRes.token, selectRes.company, selectRes.role);
        
        navigate('/');
      } else {
        message.warning('您尚未关联任何账套，请联系系统管理员分配权限。');
      }
    } catch (error: unknown) {
      console.error('Login failed:', error);
      let errorMessage = '登录失败，请检查您的系统网络。';
      
      const err = error as any;
      if (err.response) {
        if (err.response.status === 401) {
          errorMessage = '账号或密码错误，请重新输入。';
        } else if (err.response.status === 422) {
          errorMessage = '提交的格式不正确，请确保邮箱和密码均已填写。';
        } else if (err.response.data?.message) {
          errorMessage = err.response.data.message;
        }
      } else if (error instanceof Error) {
        errorMessage = error.message;
      }

      message.error(errorMessage);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#0F172A' }}>
      <Card title="财务管理系统" style={{ width: 400 }}>
        <Form onFinish={onFinish} autoComplete="off">
          <Form.Item
            name="email"
            label="邮箱"
            rules={[
              { required: true, message: '请输入邮箱！' },
              { type: 'email', message: '请输入有效的邮箱地址！' }
            ]}
          >
            <Input prefix={<UserOutlined />} placeholder="邮箱" />
          </Form.Item>
          <Form.Item
            name="password"
            label="密码"
            rules={[{ required: true, message: '请输入密码！' }]}
          >
            <Input.Password prefix={<LockOutlined />} placeholder="密码" />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" loading={loading} block>
              登录
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
};

export default Login;
