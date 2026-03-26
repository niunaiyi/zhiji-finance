# P0 Frontend Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Connect existing React frontend pages to P0 backend APIs (Auth + Foundation containers)

**Architecture:** Update existing Ant Design frontend to consume P0 backend APIs. Implement JWT authentication flow with company selection, create typed API service modules, and connect Subjects and AuxiliaryManagement pages to backend endpoints.

**Tech Stack:** React 18, TypeScript, Ant Design, Axios, React Router

---

## Context

**Backend APIs available (P0 complete):**
- Auth: `/api/v1/auth/companies` (CRUD, no tenant middleware)
- Accounts: `/api/v1/accounts` (CRUD, requires tenant)
- Aux Categories: `/api/v1/aux-categories` (CRUD, requires tenant)
- Aux Items: `/api/v1/aux-items` (CRUD, requires tenant)
- Periods: `/api/v1/periods` (CRUD + close/initialize, requires tenant)

**Frontend structure:**
- Existing: `frontend/src/` with Ant Design UI, MainLayout, pages
- API client: `frontend/src/api/client.ts` (basic axios setup, auth commented out)
- Pages: Landing.tsx, Subjects.tsx, AuxiliaryManagement.tsx, Dashboard.tsx

**Requirements:**
- Reuse existing Ant Design layout and components
- No new UI libraries
- JWT authentication with company selection
- X-Company-Id header for tenant context

---

## Phase 1: Authentication Infrastructure

### Task 1.1: Create Auth Types and API Service

**Files:**
- Create: `frontend/src/types/auth.ts`
- Create: `frontend/src/api/auth.ts`

- [ ] **Step 1: Define auth types**

```typescript
// frontend/src/types/auth.ts
export interface User {
  id: number;
  name: string;
  email: string;
}

export interface Company {
  id: number;
  code: string;
  name: string;
  fiscal_year_start: number;
  status: 'active' | 'suspended';
}

export interface UserCompanyRole {
  id: number;
  user_id: number;
  company_id: number;
  role: 'admin' | 'accountant' | 'auditor' | 'viewer';
  is_active: boolean;
}

export interface LoginResponse {
  user: User;
  companies: Company[];
}

export interface AuthState {
  user: User | null;
  token: string | null;
  company: Company | null;
  role: string | null;
  companies: Company[];
}
```

- [ ] **Step 2: Create auth API service**

```typescript
// frontend/src/api/auth.ts
import apiClient from './client';
import { LoginResponse, Company } from '../types/auth';

export const authApi = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    const response = await apiClient.post('/v1/auth/login', { email, password });
    return response.data;
  },

  selectCompany: async (companyId: number): Promise<{ token: string; company: Company; role: string }> => {
    const response = await apiClient.post('/v1/auth/select-company', { company_id: companyId });
    return response.data;
  },

  logout: async (): Promise<void> => {
    await apiClient.post('/v1/auth/logout');
  },
};
```

- [ ] **Step 3: Commit**

```bash
git add frontend/src/types/auth.ts frontend/src/api/auth.ts
git commit -m "feat(frontend): add auth types and API service"
```

---

### Task 1.2: Create Auth Context and Provider

**Files:**
- Create: `frontend/src/context/AuthContext.tsx`
- Modify: `frontend/src/App.tsx`

- [ ] **Step 1: Create AuthContext**

```typescript
// frontend/src/context/AuthContext.tsx
import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { AuthState, User, Company } from '../types/auth';

interface AuthContextType extends AuthState {
  companies: Company[];
  login: (user: User, companies: Company[]) => void;
  selectCompany: (token: string, company: Company, role: string) => void;
  logout: () => void;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [authState, setAuthState] = useState<AuthState>({
    user: null,
    token: null,
    company: null,
    role: null,
    companies: [],
  });

  // Load from localStorage on mount
  useEffect(() => {
    const storedAuth = localStorage.getItem('auth');
    if (storedAuth) {
      setAuthState(JSON.parse(storedAuth));
    }
  }, []);

  // Save to localStorage on change
  useEffect(() => {
    if (authState.token) {
      localStorage.setItem('auth', JSON.stringify(authState));
    } else {
      localStorage.removeItem('auth');
    }
  }, [authState]);

  const login = (user: User, companies: Company[]) => {
    setAuthState({ user, token: null, company: null, role: null, companies });
  };

  const selectCompany = (token: string, company: Company, role: string) => {
    setAuthState(prev => ({ ...prev, token, company, role }));
  };

  const logout = () => {
    setAuthState({ user: null, token: null, company: null, role: null, companies: [] });
    localStorage.removeItem('auth');
  };

  const isAuthenticated = !!authState.token && !!authState.company;

  return (
    <AuthContext.Provider value={{ ...authState, companies: authState.companies, login, selectCompany, logout, isAuthenticated }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};
```

- [ ] **Step 2: Wrap App with AuthProvider**

```typescript
// frontend/src/App.tsx - add AuthProvider
import { AuthProvider } from './context/AuthContext';

function App() {
  return (
    <AuthProvider>
      {/* existing app content */}
    </AuthProvider>
  );
}
```

- [ ] **Step 3: Commit**

```bash
git add frontend/src/context/AuthContext.tsx frontend/src/App.tsx
git commit -m "feat(frontend): add auth context and provider"
```

---

### Task 1.3: Update API Client with Auth Interceptors

**Files:**
- Modify: `frontend/src/api/client.ts`

- [ ] **Step 1: Add auth and company interceptors**

```typescript
// frontend/src/api/client.ts
import axios from 'axios';

const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request interceptor - add token and company ID
apiClient.interceptors.request.use((config) => {
    const authData = localStorage.getItem('auth');
    if (authData) {
        const { token, company } = JSON.parse(authData);
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        if (company) {
            config.headers['X-Company-Id'] = company.id.toString();
        }
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Response interceptor - handle 401
apiClient.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response && error.response.status === 401) {
        // Clear auth and redirect to login
        localStorage.removeItem('auth');
        window.location.href = '/login';
    }
    return Promise.reject(error);
});

export default apiClient;
```

- [ ] **Step 2: Commit**

```bash
git add frontend/src/api/client.ts
git commit -m "feat(frontend): add auth and company interceptors to API client"
```

---

### Task 1.4: Create Login and Company Selection Pages

**Files:**
- Create: `frontend/src/pages/Login.tsx`
- Create: `frontend/src/pages/CompanySelection.tsx`
- Modify: `frontend/src/App.tsx` (add routes)

- [ ] **Step 1: Create Login page**

```typescript
// frontend/src/pages/Login.tsx
import React, { useState } from 'react';
import { Form, Input, Button, Card, message } from 'antd';
import { UserOutlined, LockOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';

export const Login: React.FC = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const { login } = useAuth();

  const onFinish = async (values: { email: string; password: string }) => {
    setLoading(true);
    try {
      const response = await authApi.login(values.email, values.password);
      login(response.user, response.companies);
      navigate('/select-company');
    } catch (error) {
      message.error('Login failed. Please check your credentials.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f0f2f5' }}>
      <Card title="财务管理系统" style={{ width: 400 }}>
        <Form onFinish={onFinish} autoComplete="off">
          <Form.Item name="email" rules={[{ required: true, message: 'Please input your email!' }]}>
            <Input prefix={<UserOutlined />} placeholder="Email" />
          </Form.Item>
          <Form.Item name="password" rules={[{ required: true, message: 'Please input your password!' }]}>
            <Input.Password prefix={<LockOutlined />} placeholder="Password" />
          </Form.Item>
          <Form.Item>
            <Button type="primary" htmlType="submit" loading={loading} block>
              Login
            </Button>
          </Form.Item>
        </Form>
      </Card>
    </div>
  );
};
```

- [ ] **Step 2: Create Company Selection page**

```typescript
// frontend/src/pages/CompanySelection.tsx
import React, { useState } from 'react';
import { Card, List, Button, message } from 'antd';
import { useNavigate } from 'react-router-dom';
import { authApi } from '../api/auth';
import { useAuth } from '../context/AuthContext';

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
    <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '100vh', background: '#f0f2f5' }}>
      <Card title="Select Company" style={{ width: 600 }}>
        <List
          dataSource={companies}
          renderItem={(company: any) => (
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
                description={`Code: ${company.code} | Status: ${company.status}`}
              />
            </List.Item>
          )}
        />
      </Card>
    </div>
  );
};
```

- [ ] **Step 3: Update App.tsx with routes and protection**

```typescript
// frontend/src/App.tsx
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';
import { Login } from './pages/Login';
import { CompanySelection } from './pages/CompanySelection';
import { MainLayout } from './layouts/MainLayout';
import { Dashboard } from './pages/Dashboard';
import { Subjects } from './pages/Subjects';
import { AuxiliaryManagement } from './pages/AuxiliaryManagement';

// Protected route wrapper
const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const { isAuthenticated } = useAuth();
  return isAuthenticated ? <>{children}</> : <Navigate to="/login" />;
};

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/select-company" element={<CompanySelection />} />
          <Route path="/" element={<ProtectedRoute><MainLayout /></ProtectedRoute>}>
            <Route index element={<Dashboard />} />
            <Route path="subjects" element={<Subjects />} />
            <Route path="entities" element={<AuxiliaryManagement />} />
            {/* Add other protected routes here */}
          </Route>
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;
```

- [ ] **Step 4: Test login flow**

1. Start backend and frontend
2. Navigate to http://localhost:5173/login
3. Login with admin@example.com / password
4. Verify redirect to company selection
5. Select a company
6. Verify redirect to dashboard with token set

- [ ] **Step 5: Commit**

```bash
git add frontend/src/pages/Login.tsx frontend/src/pages/CompanySelection.tsx frontend/src/App.tsx frontend/src/context/AuthContext.tsx
git commit -m "feat(frontend): add login and company selection pages with route protection"
```

---

## Phase 2: API Service Modules

### Task 2.1: Create Account Types and API Service

**Files:**
- Create: `frontend/src/types/account.ts`
- Create: `frontend/src/api/accounts.ts`

- [ ] **Step 1: Define account types**

```typescript
// frontend/src/types/account.ts
export interface Account {
  id: number;
  company_id: number;
  code: string;
  name: string;
  parent_id: number | null;
  level: number;
  element_type: 'asset' | 'liability' | 'equity' | 'income' | 'expense' | 'cost';
  balance_direction: 'debit' | 'credit';
  is_detail: boolean;
  is_active: boolean;
  has_aux: boolean;
  created_at: string;
  updated_at: string;
}

export interface CreateAccountRequest {
  code: string;
  name: string;
  parent_id?: number;
  element_type?: 'asset' | 'liability' | 'equity' | 'income' | 'expense' | 'cost';
  balance_direction?: 'debit' | 'credit';
  has_aux?: boolean;
}

export interface UpdateAccountRequest {
  name: string;
  is_active?: boolean;
}

export interface ListAccountsParams {
  parent_id?: number;
  is_active?: boolean;
  is_detail?: boolean;
  page?: number;
  per_page?: number;
}
```

- [ ] **Step 2: Create accounts API service**

```typescript
// frontend/src/api/accounts.ts
import apiClient from './client';
import { Account, CreateAccountRequest, UpdateAccountRequest, ListAccountsParams } from '../types/account';

export const accountsApi = {
  list: async (params?: ListAccountsParams): Promise<{ data: Account[]; meta: any }> => {
    const response = await apiClient.get('/v1/accounts', { params });
    return response.data;
  },

  get: async (id: number): Promise<Account> => {
    const response = await apiClient.get(`/v1/accounts/${id}`);
    return response.data.data;
  },

  create: async (data: CreateAccountRequest): Promise<Account> => {
    const response = await apiClient.post('/v1/accounts', data);
    return response.data.data;
  },

  update: async (id: number, data: UpdateAccountRequest): Promise<Account> => {
    const response = await apiClient.put(`/v1/accounts/${id}`, data);
    return response.data.data;
  },

  deactivate: async (id: number): Promise<void> => {
    await apiClient.delete(`/v1/accounts/${id}`);
  },
};
```

- [ ] **Step 3: Commit**

```bash
git add frontend/src/types/account.ts frontend/src/api/accounts.ts
git commit -m "feat(frontend): add account types and API service"
```

---

### Task 2.2: Create Auxiliary Accounting Types and API Services

**Files:**
- Create: `frontend/src/types/auxiliary.ts`
- Create: `frontend/src/api/auxiliary.ts`

- [ ] **Step 1: Define auxiliary types**

```typescript
// frontend/src/types/auxiliary.ts
export interface AuxCategory {
  id: number;
  company_id: number;
  code: string;
  name: string;
  is_system: boolean;
  created_at: string;
  updated_at: string;
}

export interface AuxItem {
  id: number;
  company_id: number;
  aux_category_id: number;
  code: string;
  name: string;
  parent_id: number | null;
  is_active: boolean;
  extra: Record<string, any> | null;
  created_at: string;
  updated_at: string;
}

export interface CreateAuxCategoryRequest {
  code: string;
  name: string;
}

export interface CreateAuxItemRequest {
  aux_category_id: number;
  code: string;
  name: string;
  parent_id?: number;
  extra?: Record<string, any>;
}
```

- [ ] **Step 2: Create auxiliary API services**

```typescript
// frontend/src/api/auxiliary.ts
import apiClient from './client';
import { AuxCategory, AuxItem, CreateAuxCategoryRequest, CreateAuxItemRequest } from '../types/auxiliary';

export const auxCategoriesApi = {
  list: async (params?: { is_system?: boolean }): Promise<{ data: AuxCategory[] }> => {
    const response = await apiClient.get('/v1/aux-categories', { params });
    return response.data;
  },

  get: async (id: number): Promise<AuxCategory> => {
    const response = await apiClient.get(`/v1/aux-categories/${id}`);
    return response.data.data;
  },

  create: async (data: CreateAuxCategoryRequest): Promise<AuxCategory> => {
    const response = await apiClient.post('/v1/aux-categories', data);
    return response.data.data;
  },

  update: async (id: number, data: Partial<CreateAuxCategoryRequest>): Promise<AuxCategory> => {
    const response = await apiClient.put(`/v1/aux-categories/${id}`, data);
    return response.data.data;
  },
};

export const auxItemsApi = {
  list: async (params?: { aux_category_id?: number; is_active?: boolean }): Promise<{ data: AuxItem[] }> => {
    const response = await apiClient.get('/v1/aux-items', { params });
    return response.data;
  },

  get: async (id: number): Promise<AuxItem> => {
    const response = await apiClient.get(`/v1/aux-items/${id}`);
    return response.data.data;
  },

  create: async (data: CreateAuxItemRequest): Promise<AuxItem> => {
    const response = await apiClient.post('/v1/aux-items', data);
    return response.data.data;
  },

  update: async (id: number, data: Partial<CreateAuxItemRequest>): Promise<AuxItem> => {
    const response = await apiClient.put(`/v1/aux-items/${id}`, data);
    return response.data.data;
  },
};
```

- [ ] **Step 3: Commit**

```bash
git add frontend/src/types/auxiliary.ts frontend/src/api/auxiliary.ts
git commit -m "feat(frontend): add auxiliary accounting types and API services"
```

---

### Task 2.3: Create Period Types and API Service

**Files:**
- Create: `frontend/src/types/period.ts`
- Create: `frontend/src/api/periods.ts`

- [ ] **Step 1: Define period types**

```typescript
// frontend/src/types/period.ts
export interface Period {
  id: number;
  company_id: number;
  fiscal_year: number;
  period_number: number;
  start_date: string;
  end_date: string;
  status: 'open' | 'closed' | 'locked';
  closed_at: string | null;
  closed_by: number | null;
  created_at: string;
  updated_at: string;
}

export interface CreatePeriodRequest {
  fiscal_year: number;
  period_number: number;
  start_date: string;
  end_date: string;
}

export interface ListPeriodsParams {
  fiscal_year?: number;
  status?: 'open' | 'closed' | 'locked';
  page?: number;
  per_page?: number;
}

export interface InitializeFiscalYearRequest {
  fiscal_year: number;
}
```

- [ ] **Step 2: Create periods API service**

```typescript
// frontend/src/api/periods.ts
import apiClient from './client';
import { Period, CreatePeriodRequest, ListPeriodsParams, InitializeFiscalYearRequest } from '../types/period';

export const periodsApi = {
  list: async (params?: ListPeriodsParams): Promise<{ data: Period[]; meta: any }> => {
    const response = await apiClient.get('/v1/periods', { params });
    return response.data;
  },

  get: async (id: number): Promise<Period> => {
    const response = await apiClient.get(`/v1/periods/${id}`);
    return response.data.data;
  },

  create: async (data: CreatePeriodRequest): Promise<Period> => {
    const response = await apiClient.post('/v1/periods', data);
    return response.data.data;
  },

  close: async (id: number): Promise<Period> => {
    const response = await apiClient.post(`/v1/periods/${id}/close`);
    return response.data.data;
  },

  initializeFiscalYear: async (data: InitializeFiscalYearRequest): Promise<{ data: Period[] }> => {
    const response = await apiClient.post('/v1/fiscal-years/initialize', data);
    return response.data;
  },
};
```

- [ ] **Step 3: Commit**

```bash
git add frontend/src/types/period.ts frontend/src/api/periods.ts
git commit -m "feat(frontend): add period types and API service"
```

---

## Phase 3: Connect Pages to APIs

### Task 3.1: Connect Subjects Page to Accounts API

**Files:**
- Modify: `frontend/src/pages/Subjects.tsx`

- [ ] **Step 1: Add API integration to Subjects page**

**First, examine the existing Subjects.tsx:**
- Check if it has mock/hardcoded data - if so, remove it
- Identify the table/tree component used for displaying accounts
- Locate any existing form components for create/edit operations

**Then, add API integration:**
- Import API service and types at the top
- Add state for accounts, loading, and error handling
- Add useEffect to fetch accounts on mount
- Replace any mock data with API-fetched data
- Update create/edit/delete handlers to call API methods
- Add loading spinners and error messages

Key code to add:
```typescript
import { accountsApi } from '../api/accounts';
import { Account } from '../types/account';
import { message, Spin } from 'antd';

// Add state
const [accounts, setAccounts] = useState<Account[]>([]);
const [loading, setLoading] = useState(false);

// Fetch on mount
useEffect(() => {
  fetchAccounts();
}, []);

const fetchAccounts = async () => {
  setLoading(true);
  try {
    const response = await accountsApi.list();
    setAccounts(response.data);
  } catch (error) {
    message.error('Failed to load accounts');
  } finally {
    setLoading(false);
  }
};

// Create handler
const handleCreate = async (values: CreateAccountRequest) => {
  try {
    await accountsApi.create(values);
    message.success('Account created');
    fetchAccounts();
  } catch (error) {
    message.error('Failed to create account');
  }
};
```

- [ ] **Step 2: Test in browser**

1. Start backend: `php artisan serve`
2. Start frontend: `cd frontend && npm run dev`
3. Login and select company
4. Navigate to Subjects page
5. Verify accounts load from API
6. Test create, edit, deactivate operations

- [ ] **Step 3: Commit**

```bash
git add frontend/src/pages/Subjects.tsx
git commit -m "feat(frontend): connect Subjects page to Accounts API"
```

---

### Task 3.2: Connect AuxiliaryManagement Page to Auxiliary APIs

**Files:**
- Modify: `frontend/src/pages/AuxiliaryManagement.tsx`

- [ ] **Step 1: Add API integration to AuxiliaryManagement page**

**First, examine the existing AuxiliaryManagement.tsx:**
- Check if it has mock/hardcoded data for categories and items - if so, remove it
- Identify the UI components used for displaying categories and items
- Locate any existing form components

**Then, add API integration:**
- Import API services and types at the top
- Add state for categories, items, selected category, and loading
- Add useEffect to fetch categories on mount
- Add useEffect to fetch items when category is selected
- Replace any mock data with API-fetched data
- Update create/edit handlers to call API methods
- Add loading spinners and error messages

Key code to add:
```typescript
import { auxCategoriesApi, auxItemsApi } from '../api/auxiliary';
import { AuxCategory, AuxItem } from '../types/auxiliary';

const [categories, setCategories] = useState<AuxCategory[]>([]);
const [items, setItems] = useState<AuxItem[]>([]);
const [selectedCategory, setSelectedCategory] = useState<number | null>(null);

useEffect(() => {
  fetchCategories();
}, []);

useEffect(() => {
  if (selectedCategory) {
    fetchItems(selectedCategory);
  }
}, [selectedCategory]);

const fetchCategories = async () => {
  const response = await auxCategoriesApi.list();
  setCategories(response.data);
};

const fetchItems = async (categoryId: number) => {
  const response = await auxItemsApi.list({ aux_category_id: categoryId });
  setItems(response.data);
};
```

- [ ] **Step 2: Test in browser**

1. Navigate to Auxiliary Management page
2. Verify categories load (customer, supplier, dept, employee, inventory, project)
3. Select a category and verify items load
4. Test create/edit item operations
5. Verify system categories cannot be edited

- [ ] **Step 3: Commit**

```bash
git add frontend/src/pages/AuxiliaryManagement.tsx
git commit -m "feat(frontend): connect AuxiliaryManagement page to Auxiliary APIs"
```

---

## Phase 4: Testing and Polish

### Task 4.1: Add Error Boundary and Loading States

**Files:**
- Create: `frontend/src/components/ErrorBoundary.tsx`
- Modify: `frontend/src/App.tsx`

- [ ] **Step 1: Create ErrorBoundary component**

```typescript
// frontend/src/components/ErrorBoundary.tsx
import React, { Component, ReactNode } from 'react';
import { Result, Button } from 'antd';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error('ErrorBoundary caught:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return (
        <Result
          status="error"
          title="Something went wrong"
          subTitle={this.state.error?.message}
          extra={
            <Button type="primary" onClick={() => window.location.reload()}>
              Reload Page
            </Button>
          }
        />
      );
    }

    return this.props.children;
  }
}
```

- [ ] **Step 2: Wrap App with ErrorBoundary**

```typescript
// frontend/src/App.tsx
import { ErrorBoundary } from './components/ErrorBoundary';

function App() {
  return (
    <ErrorBoundary>
      <AuthProvider>
        {/* existing app content */}
      </AuthProvider>
    </ErrorBoundary>
  );
}
```

- [ ] **Step 3: Commit**

```bash
git add frontend/src/components/ErrorBoundary.tsx frontend/src/App.tsx
git commit -m "feat(frontend): add error boundary for graceful error handling"
```

---

### Task 4.2: Integration Testing

**Files:**
- Create: `frontend/src/__tests__/integration/api.test.ts`

- [ ] **Step 1: Create integration tests with auth setup**

```typescript
// frontend/src/__tests__/integration/api.test.ts
import { accountsApi } from '../../api/accounts';
import { auxCategoriesApi, auxItemsApi } from '../../api/auxiliary';
import { authApi } from '../../api/auth';

describe('API Integration Tests', () => {
  // Setup: Login and select company before running tests
  beforeAll(async () => {
    // Login with test credentials (from CompanySeeder)
    const loginResponse = await authApi.login('admin@example.com', 'password');

    // Select first available company
    const company = loginResponse.companies[0];
    const selectResponse = await authApi.selectCompany(company.id);

    // Store auth data in localStorage for API client interceptors
    localStorage.setItem('auth', JSON.stringify({
      user: loginResponse.user,
      token: selectResponse.token,
      company: selectResponse.company,
      role: selectResponse.role,
    }));
  });

  // Cleanup: Clear auth after tests
  afterAll(() => {
    localStorage.removeItem('auth');
  });

  describe('Accounts API', () => {
    it('should list accounts', async () => {
      const response = await accountsApi.list();
      expect(response.data).toBeInstanceOf(Array);
    });

    it('should create and retrieve account', async () => {
      const newAccount = await accountsApi.create({
        code: '9999',
        name: 'Test Account',
      });
      expect(newAccount.code).toBe('9999');

      const retrieved = await accountsApi.get(newAccount.id);
      expect(retrieved.name).toBe('Test Account');
    });
  });

  describe('Auxiliary API', () => {
    it('should list aux categories', async () => {
      const response = await auxCategoriesApi.list();
      expect(response.data).toBeInstanceOf(Array);
      expect(response.data.length).toBeGreaterThan(0);
    });

    it('should create aux item', async () => {
      const categories = await auxCategoriesApi.list();
      const category = categories.data[0];

      const newItem = await auxItemsApi.create({
        aux_category_id: category.id,
        code: 'TEST001',
        name: 'Test Item',
      });
      expect(newItem.code).toBe('TEST001');
    });
  });
});
```

- [ ] **Step 2: Run integration tests**

```bash
cd frontend
npm test -- api.test.ts
```

Expected: All tests pass (requires backend running and seeded database)

- [ ] **Step 3: Commit**

```bash
git add frontend/src/__tests__/integration/api.test.ts
git commit -m "test(frontend): add API integration tests"
```

---

## Success Criteria

- ✅ Users can login and select company
- ✅ JWT token and company ID automatically added to API requests
- ✅ Subjects page loads accounts from backend API
- ✅ Subjects page can create/edit/deactivate accounts
- ✅ AuxiliaryManagement page loads categories and items from backend
- ✅ AuxiliaryManagement page can create/edit items
- ✅ Error handling with user-friendly messages
- ✅ Loading states during API calls
- ✅ Integration tests pass

---

## Notes

**API Response Format:**
Backend returns data in this format:
```json
{
  "data": { /* single resource */ },
  "meta": { /* pagination, etc */ }
}
```

Or for lists:
```json
{
  "data": [ /* array of resources */ ],
  "meta": { "pagination": { "total": 100, "page": 1 } }
}
```

**Authentication Flow:**
1. POST /api/v1/auth/login → returns user + companies list
2. User selects company from list
3. POST /api/v1/auth/select-company → returns JWT token
4. Token stored in localStorage
5. All subsequent requests include `Authorization: Bearer {token}` and `X-Company-Id: {id}` headers

**Tenant Middleware:**
All `/api/v1/accounts`, `/api/v1/aux-*`, `/api/v1/periods` endpoints require both:
- `Authorization: Bearer {token}` header
- `X-Company-Id: {id}` header

Auth endpoints (`/api/v1/auth/*`) do NOT require tenant middleware.
