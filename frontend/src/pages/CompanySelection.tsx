import React, { useState } from 'react';
import { Card, List, Button, message, Tag } from 'antd';
import { useNavigate } from 'react-router-dom';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';
import { Company } from '../types/auth';

export const CompanySelection: React.FC = () => {
  const [loading, setLoading] = useState<number | null>(null);
  const navigate = useNavigate();
  const { companies, selectCompany } = useAuth();

  const handleSelectCompany = async (companyId: number) => {
    setLoading(companyId);
    try {
      const response = await authApi.selectCompany(companyId);
      selectCompany(response.token, response.company, response.role);
      navigate('/');
    } catch (error) {
      message.error('Failed to select company');
    } finally {
      setLoading(null);
    }
  };

  return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#0F172A' }}>
      <Card title="Select Company" style={{ width: 600 }}>
        <List
          dataSource={companies}
          renderItem={(company: Company) => (
            <List.Item
              actions={[
                <Button
                  type="primary"
                  loading={loading === company.id}
                  onClick={() => handleSelectCompany(company.id)}
                >
                  Select
                </Button>
              ]}
            >
              <List.Item.Meta
                title={company.name}
                description={
                  <div>
                    <span>Code: {company.code}</span>
                    <span style={{ marginLeft: 16 }}>
                      Status: <Tag color={company.status === 'active' ? 'green' : 'red'}>{company.status}</Tag>
                    </span>
                  </div>
                }
              />
            </List.Item>
          )}
        />
      </Card>
    </div>
  );
};

export default CompanySelection;
