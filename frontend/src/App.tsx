import React from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ConfigProvider, theme } from 'antd';
import { BookProvider } from './context/BookContext';
import { TabProvider } from './context/TabContext';
import MainLayout from './layouts/MainLayout';
import Dashboard from './pages/Dashboard';
import Vouchers from './pages/Vouchers';
import Subjects from './pages/Subjects';
import Reports from './pages/Reports';
import PeriodEnd from './pages/PeriodEnd';
import Assets from './pages/Assets';
import CashFlowReport from './pages/reports/CashFlowReport';
import AgingAnalysis from './pages/reports/AgingAnalysis';
import AuxiliaryManagement from './pages/AuxiliaryManagement';
import InventoryManagement from './pages/InventoryManagement';
import InventoryReport from './pages/reports/InventoryReport';
import DictionaryManagement from './pages/DictionaryManagement';
import Login from './pages/Login';
import CompanySelection from './pages/CompanySelection';
import { SettingsProvider, useSettings } from './context/SettingsContext';
import { AuthProvider, useAuth } from './context/AuthContext';
import './index.css';

// Protected route wrapper
const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" replace />;
};

const AppContent: React.FC = () => {
  const { tableSize } = useSettings();

  return (
    <ConfigProvider
      componentSize={tableSize === 'default' ? undefined : tableSize}
      theme={{
        algorithm: theme.darkAlgorithm,
        token: {
          colorPrimary: '#F59E0B',
          borderRadius: 8,
          fontFamily: "'IBM Plex Sans', sans-serif",
        },
        components: {
          Layout: {
            bodyBg: '#0F172A',
            headerBg: '#1E293B',
            siderBg: '#1E293B',
          },
          Card: {
            colorBgContainer: 'rgba(30, 41, 59, 0.7)',
          },
          Table: {
            padding: tableSize === 'small' ? 8 : (tableSize === 'middle' ? 12 : 16),
            paddingContentVertical: tableSize === 'small' ? 6 : (tableSize === 'middle' ? 8 : 12),
          },
        },
      }}
    >
      <BookProvider>
        <BrowserRouter>
          <TabProvider>
            <Routes>
              <Route path="/login" element={<Login />} />
              <Route path="/select-company" element={<CompanySelection />} />
              <Route path="/" element={<ProtectedRoute><MainLayout /></ProtectedRoute>}>
                <Route index element={<Dashboard />} />
                <Route path="vouchers" element={<Vouchers />} />
                <Route path="subjects" element={<Subjects />} />
                <Route path="reports" element={<Reports />} />
                <Route path="reports/cash-flow" element={<CashFlowReport />} />
                <Route path="reports/aging" element={<AgingAnalysis />} />
                <Route path="reports/inventory" element={<InventoryReport />} />
                <Route path="entities" element={<AuxiliaryManagement />} />
                <Route path="inventory" element={<InventoryManagement />} />
                <Route path="period-end" element={<PeriodEnd />} />
                <Route path="assets" element={<Assets />} />
                <Route path="settings" element={<DictionaryManagement />} />
              </Route>
            </Routes>
          </TabProvider>
        </BrowserRouter>
      </BookProvider>
    </ConfigProvider>
  );
};

const App: React.FC = () => {
  return (
    <SettingsProvider>
      <AuthProvider>
        <AppContent />
      </AuthProvider>
    </SettingsProvider>
  );
};

export default App;
